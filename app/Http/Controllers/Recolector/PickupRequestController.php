<?php

namespace App\Http\Controllers\Recolector;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseNotificationService; // Nuestro servicio de envío
use Illuminate\Support\Facades\Log;          // Para loguear errores
use Exception;

class PickupRequestController extends Controller
{
    /**
     * Muestra los detalles de una solicitud de recojo.
     */
    public function show(PickupRequest $pickupRequest)
    {
        // Cargamos las relaciones para poder ver los items y sus fotos
        $pickupRequest->load('items.photos');

        return view('recolector.requests.show', [
            'request' => $pickupRequest,
        ]);
    }

    public function accept(PickupRequest $pickupRequest, FirebaseNotificationService $firebaseService)
    {
        // Actualizamos el estado y asignamos el recolector logueado
        $pickupRequest->update([
            'status' => 'aceptado',
            'collector_id' => Auth::id(),
        ]);
        try {
            // Obtenemos el cliente que creó la solicitud (usando la relación Eloquent)
            // Asegúrate de tener definida la relación belongsTo('user') en tu modelo PickupRequest
            $client = $pickupRequest->user;

            if ($client && $client->fcm_token) {
                $title = "¡Tu solicitud ha sido aceptada!";
                // Obtenemos el nombre del recolector que aceptó
                $collectorName = Auth::user()->name ?? 'un recolector'; // Usamos el nombre del recolector logueado
                $body = "{$collectorName} ha aceptado tu solicitud de recojo.";

                Log::debug("Intentando notificar al cliente [ID: {$client->id}] con token: " . substr($client->fcm_token, 0, 10) . "...");

                // Usamos el servicio para enviar la notificación
                $success = $firebaseService->sendNotification($client->fcm_token, $title, $body);

                if ($success) {
                    Log::debug("-> Notificación de aceptación enviada con éxito al cliente.");
                } else {
                    Log::warning("-> Fallo al enviar notificación de aceptación al cliente [ID: {$client->id}].");
                }
            } else {
                Log::warning("No se pudo notificar al cliente para la solicitud [ID: {$pickupRequest->id}]. Cliente no encontrado o sin token FCM.");
            }
        } catch (Exception $e) {
            Log::error('Error al intentar notificar al cliente sobre aceptación: ' . $e->getMessage(), ['exception' => $e]);
        }
        // --- FIN DE LA LÓGICA DE NOTIFICACIÓN ---


        // 4. Redirigimos al siguiente paso (formulario de programar horario)
        return redirect()->route('recolector.request.schedule.show', $pickupRequest)
            ->with('success', '¡Has aceptado la solicitud! Ahora propone un horario.'); // Mensaje flash mejorado
    }

    public function showScheduleForm(PickupRequest $pickupRequest)
    {
        if ($pickupRequest->collector_id !== Auth::id()) {
            abort(403, 'No tienes permiso para programar esta solicitud.');
        }
        return view('recolector.requests.schedule', ['request' => $pickupRequest]);
    }

    public function storeSchedule(Request $request, PickupRequest $pickupRequest, FirebaseNotificationService $firebaseService)
    {
        if ($pickupRequest->collector_id !== Auth::id()) {
            abort(403, 'No tienes permiso para programar esta solicitud.');
        }
        $validatedData = $request->validate([
            'proposed_date' => 'required|date|after_or_equal:today',
            'proposed_time_start' => 'required|date_format:H:i',
            'proposed_time_end' => 'required|date_format:H:i|after:proposed_time_start',
        ]);

        $pickupRequest->update($validatedData);

        try {
            $client = $pickupRequest->user;
            if ($client && $client->fcm_token) {
                $title = "¡Horario Propuesto!";
                $body = "El recolector ha propuesto un horario para tu recojo. Por favor, revísalo y confírmalo o recházalo desde tu panel."; // Mensaje más claro

                Log::debug("[Schedule] Intentando notificar al cliente [ID: {$client->id}] Token: " . substr($client->fcm_token, 0, 10) . "...");
                $success = $firebaseService->sendNotification($client->fcm_token, $title, $body);
                if ($success) {
                    Log::debug("[Schedule] -> Notificación enviada con éxito al cliente.");
                } else {
                    Log::warning("[Schedule] -> Fallo al enviar notificación al cliente [ID: {$client->id}].");
                }
            } else {
                Log::warning("[Schedule] No se pudo notificar al cliente para la solicitud [ID: {$pickupRequest->id}]. Cliente no encontrado o sin token FCM.");
            }
        } catch (Exception $e) {
            Log::error('[Schedule] Error al notificar horario propuesto al cliente: ' . $e->getMessage(), ['exception' => $e]);
        }

        return redirect()->route('recolector.dashboard')->with('success', 'Horario propuesto enviado al cliente para su confirmación.');
    }






    public function setInProgress(PickupRequest $pickupRequest, FirebaseNotificationService $firebaseService)
    {
        if ($pickupRequest->collector_id !== Auth::id()) {
            abort(403);
        }
        $pickupRequest->update(['status' => 'en_camino']);
        try {
            $client = $pickupRequest->user;
            if ($client && $client->fcm_token) {
                $title = "¡Tu recolector está en camino!";
                $body = "El recolector asignado a tu solicitud ya se dirige a tu ubicación.";

                Log::debug("[InProgress] Intentando notificar al cliente [ID: {$client->id}] Token: " . substr($client->fcm_token, 0, 10) . "...");
                $success = $firebaseService->sendNotification($client->fcm_token, $title, $body);
                if ($success) {
                    Log::debug("[InProgress] -> Notificación enviada con éxito al cliente.");
                } else {
                    Log::warning("[InProgress] -> Fallo al enviar notificación al cliente [ID: {$client->id}].");
                }
            } else {
                Log::warning("[InProgress] No se pudo notificar al cliente para la solicitud [ID: {$pickupRequest->id}]. Cliente no encontrado o sin token FCM.");
            }
        } catch (Exception $e) {
            Log::error('[InProgress] Error al notificar \'en camino\' al cliente: ' . $e->getMessage(), ['exception' => $e]);
        }
        // --- FIN NOTIFICACIÓN ---

        // 4. Redirigimos de vuelta
        return back()->with('success', 'El cliente ha sido notificado que estás en camino.');
    }

    public function setCompleted(PickupRequest $pickupRequest, FirebaseNotificationService $firebaseService)
    {
        if ($pickupRequest->collector_id !== Auth::id()) {
            abort(403);
        }

        $pickupRequest->update(['status' => 'completado']);


        $client = $pickupRequest->user;
        $client = $pickupRequest->user;
        if ($client) {
            $puntosGanados = 10; 
            $client->points_balance += $puntosGanados; 
            $client->save(); 

            try {
                if ($client->fcm_token) {
                    $title = "¡Recojo Completado!";
                    $body = "Tu solicitud de recojo ha sido completada. ¡Has ganado {$puntosGanados} puntos!"; // Mensaje dinámico

                    Log::debug("[Completed] Intentando notificar al cliente [ID: {$client->id}] Token: " . substr($client->fcm_token, 0, 10) . "...");
                    $success = $firebaseService->sendNotification($client->fcm_token, $title, $body);
                    if ($success) {
                        Log::debug("[Completed] -> Notificación enviada con éxito al cliente.");
                    } else {
                        Log::warning("[Completed] -> Fallo al enviar notificación al cliente [ID: {$client->id}].");
                    }
                } else {
                    Log::warning("[Completed] No se pudo notificar al cliente para la solicitud [ID: {$pickupRequest->id}]. Cliente sin token FCM.");
                }
            } catch (Exception $e) {
                Log::error('[Completed] Error al notificar \'completado\' al cliente: ' . $e->getMessage(), ['exception' => $e]);
            }
            // --- FIN NOTIFICACIÓN ---
        } else {
            Log::warning("[Completed] No se encontró cliente para asignar puntos en solicitud [ID: {$pickupRequest->id}].");
        }


        return redirect()->route('recolector.my-pickups')->with('success', '¡Recojo completado exitosamente! Puntos asignados al cliente.');
    }
}
