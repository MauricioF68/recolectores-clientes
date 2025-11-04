<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardClaim;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Services\FirebaseNotificationService; // <-- AÑADIR ESTA
use Illuminate\Support\Facades\Log;           // <-- AÑADIR ESTA
use Exception;

class RewardClaimController extends Controller
{
    public function index()
    {
        // Buscamos los reclamos con estado 'solicitado' y cargamos la información relacionada
        $claims = RewardClaim::where('status', 'solicitado')
            ->with(['user', 'reward']) // Carga el usuario y la recompensa
            ->latest()
            ->get();

        return view('admin.claims.index', ['claims' => $claims]);
    }

    public function fulfill(Request $request, RewardClaim $claim, FirebaseNotificationService $firebaseService)
    {
        $validatedData = $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'tracking_code' => 'nullable|string|max:255',
            'voucher_photo' => 'nullable|image|max:2048',
            'agency_address' => 'nullable|string|max:255',
            'pickup_password' => 'nullable|string|max:255',
        ]);

        $voucherPath = $claim->voucher_path;
        if ($request->hasFile('voucher_photo')) {
            // Delete old voucher if exists and new one is uploaded
            if ($claim->voucher_path) {
                Storage::disk('public')->delete($claim->voucher_path);
            }
            $voucherPath = $request->file('voucher_photo')->store('vouchers', 'public');
        }

        // --- START OF CORRECTION ---
        // Use ?? null to safely access nullable fields from validated data
        $claim->update([
            'tracking_number' => $validatedData['tracking_number'] ?? null,
            'tracking_code' => $validatedData['tracking_code'] ?? null,
            'voucher_path' => $voucherPath,
            'agency_address' => $validatedData['agency_address'] ?? null,
            'pickup_password' => $validatedData['pickup_password'] ?? null,
            'status' => 'enviado',
        ]);
        // --- END OF CORRECTION ---

        try {
            // Usamos la relación 'user' que ya confirmamos que existe
            $client = $claim->user;

            if ($client && $client->fcm_token) {
                $title = "¡Tu recompensa está en camino!";

                // Creamos un cuerpo dinámico con los datos que llenó el admin
                // (Usamos los datos de $validatedData)
                $agency = $validatedData['agency_address'] ?? 'la agencia';
                $tracking = $validatedData['tracking_number'] ?? null;
                $password = $validatedData['pickup_password'] ?? null;

                $body = "Tu premio ha sido enviado por {$agency} y llegará en aprox. 3 días.";

                // Añadimos info extra si el admin la proporcionó
                if ($tracking) {
                    $body .= " N° de seguimiento: {$tracking}.";
                }
                if ($password) {
                    $body .= " Clave de recojo: {$password}.";
                }

                Log::debug("[RewardFulfill] Notificando al cliente [ID: {$client->id}]...");

                // Enviamos la notificación
                $success = $firebaseService->sendNotification($client->fcm_token, $title, $body);

                if (!$success) {
                    Log::warning("[RewardFulfill] Fallo al enviar notificación al cliente [ID: {$client->id}].");
                }
            } else {
                Log::warning("[RewardFulfill] No se pudo notificar al cliente para el reclamo [ID: {$claim->id}]. Cliente no encontrado o sin token FCM.");
            }
        } catch (Exception $e) {
            // Si la notificación falla, no rompemos la app, solo lo logueamos
            Log::error('[RewardFulfill] Error al notificar envío al cliente: ' . $e->getMessage(), ['exception' => $e]);
        }
        // --- FIN DE NOTIFICACIÓN ---

        // 4. Redirigimos al admin
        return redirect()->route('admin.claims.index')->with('success', 'Comprobante enviado y cliente notificado correctamente.');
    }
}
