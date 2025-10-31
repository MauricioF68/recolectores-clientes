<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;
use Exception;                                // <-- AÑADIR ESTA (para el try/catch)
use Illuminate\Support\Facades\Log;
use App\Models\Reward;
use App\Models\User;
use App\Models\RewardClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class RewardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Buscamos la última solicitud del usuario para obtener su dirección más reciente
        $lastRequest = $user->pickupRequests()->latest()->first();

        // Buscamos todas las recompensas
        $rewards = Reward::orderBy('points_cost', 'asc')->get();

        // Devolvemos la vista, pasándole las recompensas y la última ubicación
        return view('client.rewards.index', [
            'rewards' => $rewards,
            'last_location' => $lastRequest ? $lastRequest->only(['address', 'latitude', 'longitude', 'department', 'province', 'district']) : null,
        ]);
    }

    // ... (al final de la clase, después del método index)

    public function redeem(Request $request, Reward $reward, FirebaseNotificationService $firebaseService)
    {
        // 1. Validamos que la dirección haya sido enviada
        $validatedData = $request->validate([
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'department' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'dni' => 'required|string|digits:8', // DNI del cliente
            'phone' => 'required|string|max:15',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 2. Verificación de seguridad: ¿Tiene suficientes puntos?
        if ($user->points_balance < $reward->points_cost) {
            return back()->with('error', 'No tienes suficientes puntos para canjear esta recompensa.');
        }

        // 3. Usamos una transacción para asegurar que ambas operaciones (descontar y crear) ocurran
        DB::transaction(function () use ($user, $reward, $validatedData, $firebaseService) {
            // 3.1 Descontamos los puntos al usuario
            $user->dni = $validatedData['dni'];
            $user->phone = $validatedData['phone'];
            $user->points_balance -= $reward->points_cost;
            $user->save();

            // 3.2 Creamos el registro del canje
            RewardClaim::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_cost,
                'status' => 'solicitado',
                'shipping_address' => $validatedData['address'],
                'shipping_latitude' => $validatedData['latitude'],
                'shipping_longitude' => $validatedData['longitude'],
                'shipping_department' => $validatedData['department'],
                'shipping_province' => $validatedData['province'],
                'shipping_district' => $validatedData['district'],
            ]);
        });

        // --- 3.3 INICIO DE NOTIFICACIÓN A ADMINS ---
        try {
            // Buscamos a TODOS los admins con token
            $admins = User::where('role', 'administrador')
                ->whereNotNull('fcm_token')
                ->get();

            if ($admins->isEmpty()) {
                Log::warning("[RewardClaim] No se encontraron admins con token FCM para notificar.");
                return; // Salimos del bloque try, la transacción continuará
            }

            // Preparamos el mensaje
            $clientName = $user->name;
            $rewardName = $reward->name;
            $pointsUsed = $reward->points_cost;

            $title = "¡Nueva Solicitud de Canje!";
            $body = "El cliente '{$clientName}' ha solicitado canjear '{$rewardName}' ({$pointsUsed} pts).";

            // Enviamos la notificación a cada admin
            foreach ($admins as $admin) {
                Log::debug("[RewardClaim] Notificando al admin [ID: {$admin->id}]...");
                $success = $firebaseService->sendNotification($admin->fcm_token, $title, $body);
                if (!$success) {
                    Log::warning("[RewardClaim] Fallo al notificar al admin [ID: {$admin->id}].");
                }
            }
        } catch (Exception $e) {
            // Si las notificaciones fallan, NO detenemos la transacción.
            // Solo lo registramos.
            Log::error('[RewardClaim] Error al notificar a los admins: ' . $e->getMessage());
        }
        // --- FIN DE NOTIFICACIÓN ---

        // Fin de la transacción

        // 4. Redirigimos con un mensaje de éxito
        return redirect()->route('client.rewards.index')->with('success', '¡Recompensa canjeada con éxito! Se ha notificado al administrador.');
    }

    public function myClaims()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Buscamos todos los reclamos del usuario, cargando la info de la recompensa
        $claims = RewardClaim::where('user_id', $user->id)
            ->with('reward') // Carga la info de la recompensa asociada
            ->latest() // Ordena por el más reciente
            ->get();

        return view('client.rewards.my', ['claims' => $claims]);
    }
}
