<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseNotificationService;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class PickupRequestController extends Controller
{
    /**
     * Muestra el Paso 1: Formulario del Mapa.
     */
    public function createStepOne()
    {
        return view('client.requests.step-one');
    }

    /**
     * Procesa y guarda los datos del Paso 1 en la sesión.
     */
    public function postStepOne(Request $request)
    {
        // 1. Validamos los datos de la ubicación
        $validatedData = $request->validate([
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'notes' => 'nullable|string|max:500',
            'department' => 'nullable|string',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
        ]);

        // 2. Guardamos los datos en la sesión
        $request->session()->put('pickup_request.step_one', $validatedData);

        // 3. Redirigimos al (futuro) Paso 2
        // return redirect()->route('client.request.step-two.create'); // Lo activaremos después
        return redirect()->route('client.request.step-two.create');
    }

    public function createStepTwo()
    {
        // Verificamos que existan datos del paso 1 en la sesión
        if (!session()->has('pickup_request.step_one')) {
            return redirect()->route('client.request.step-one.create');
        }

        return view('client.requests.step-two');
    }

    /**
     * Almacena la solicitud (Paso 2) Y ENVÍA NOTIFICACIONES
     *
     * --- AÑADIMOS PHPDOC PARA $firebaseService ---
     * @param Request $request
     * @param FirebaseNotificationService $firebaseService  // <-- Esto le dice al editor el tipo
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(Request $request, FirebaseNotificationService $firebaseService)
    {

        // 1. Validamos los datos del Paso 2
        $validatedStepTwo = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.waste_type' => 'required|string',
            'items.*.bag_quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string',
            'items.*.photos' => 'nullable|array',
            'items.*.photos.*' => 'image|mimes:jpeg,png,jpg|max:5120' // Valida cada foto
        ]);

        // 2. Recuperamos los datos del Paso 1 de la sesión
        $stepOneData = $request->session()->get('pickup_request.step_one');
        /** @var PickupRequest|null $pickupRequest Definimos que puede ser PickupRequest o null inicialmente */
        $pickupRequest = null;

        // 3. Usamos una transacción para asegurar que todo se guarde correctamente
        DB::transaction(function () use ($stepOneData, $validatedStepTwo, $request, &$pickupRequest) {

            $mainRequestData = array_merge($stepOneData, ['status' => 'pendiente']);

            // 3.1 Creamos la solicitud principal
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $pickupRequest = $user->pickupRequests()->create($mainRequestData);

            // 3.2 Creamos cada item de basura
            foreach ($validatedStepTwo['items'] as $index => $itemData) {
                $item = $pickupRequest->items()->create([
                    'waste_type' => $itemData['waste_type'],
                    'bag_quantity' => $itemData['bag_quantity'],
                    'note' => $itemData['note'],
                ]);

                // 3.3 (Opcional) Manejo de subida de fotos
                if ($request->hasFile("items.{$index}.photos")) {
                    foreach ($request->file("items.{$index}.photos") as $photo) {
                        // Guardamos la foto en 'storage/app/public/photos' y obtenemos su ruta
                        $path = $photo->store('photos', 'public');

                        // Creamos el registro en la base de datos
                        $item->photos()->create(['path' => $path]);
                    }
                }
            }
        });

        // 4. Limpiamos los datos de la sesión
        $request->session()->forget('pickup_request.step_one');

        if ($pickupRequest) {
            $this->notifyCollectorsByDistance($firebaseService, $pickupRequest);
        }

        // 5. Redirigimos con mensaje de éxito
        return redirect()->route('dashboard')->with('success', '¡Tu solicitud de recojo ha sido creada con éxito!');
    }

    /**
     * Busca recolectores y les notifica basado en la distancia.
     *
     * --- AÑADIMOS PHPDOC PARA LOS PARÁMETROS ---
     * @param FirebaseNotificationService $firebaseService // <-- Tipo del servicio
     * @param PickupRequest $solicitud                 // <-- Tipo de la solicitud
     * @return void                                      // <-- No devuelve nada
     */
    private function notifyCollectorsByDistance(FirebaseNotificationService $firebaseService, PickupRequest $solicitud)
    {
        Log::debug('--- Iniciando notifyCollectorsByDistance ---');
        try {
            $latSolicitud = $solicitud->latitude;
            $lonSolicitud = $solicitud->longitude;
            $distanciaCercana = 10; // Límite en KM para "cerca" (puedes cambiarlo)
            $titulo = "¡Nueva Solicitud de Recojo!";
            Log::debug("Buscando recolectores cerca de Lat: {$latSolicitud}, Lon: {$lonSolicitud}");

            // --- ¡CONSULTA CORREGIDA! ---
            // 1. Empezamos con la tabla 'users'
            $recolectores = User::where('role', 'recolector')
                ->join('collectors_master_list as cml', 'users.dni', '=', 'cml.dni')
                ->whereNotNull('users.fcm_token') // Solo los que pueden recibir notificaciones
                ->whereNotNull('users.dni')      // Y tienen DNI para poder unirlos

                // 2. Unimos con 'collectors_master_list' usando la columna 'dni'


                // 3. Nos aseguramos de que el recolector tenga coordenadas
                ->whereNotNull('cml.latitude')
                ->whereNotNull('cml.longitude')

                ->select('users.fcm_token')

                // 4a. ...y AÑADIMOS la fórmula de Haversine usando selectRaw
                ->selectRaw(
                    "( 6371 * acos( cos( radians(?) ) *
                    cos( radians( cml.latitude ) ) *
                    cos( radians( cml.longitude ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( cml.latitude ) ) )
                    ) AS distancia_en_km",
                    [$latSolicitud, $lonSolicitud, $latSolicitud] // <-- Pasamos los bindings aquí
                )

                // 6. Ordenamos por distancia (el más cercano primero)
                ->orderBy('distancia_en_km', 'asc')
                // 7. Obtenemos los resultados
                ->get();

            Log::debug("Recolectores encontrados: " . $recolectores->count());

            // 4. Enviamos la notificación personalizada a cada uno
            foreach ($recolectores as $recolector) {
                Log::debug("Procesando recolector con token: " . substr($recolector->fcm_token, 0, 10) . "..., Distancia: " . $recolector->distancia_en_km);

                // 5. Creamos el mensaje personalizado
                $distanciaKm = round($recolector->distancia_en_km, 1); // Redondeamos para el mensaje

                $body = ($recolector->distancia_en_km <= $distanciaCercana)
                    ? "¡Tenemos un recojo MUY CERCA de ti! (a {$distanciaKm} km)"
                    : "¡Tenemos un nuevo recojo disponible para ti.";

                Log::debug("Enviando notificación: '{$body}'");
                $success = $firebaseService->sendNotification($recolector->fcm_token, $titulo, $body);
                // 6. Usamos nuestro servicio para enviar
                if (!$success) {
                    Log::warning("Fallo al enviar notificación al token: " . substr($recolector->fcm_token, 0, 10) . "..."); // <-- NUEVO LOG si falla
                }
            }
            Log::debug('--- Finalizado notifyCollectorsByDistance (sin excepciones) ---');
        } catch (Exception $e) {
            // Si las notificaciones fallan, no rompemos la app, solo lo logueamos.
            Log::error('Error al enviar notificaciones por distancia: ' . $e->getMessage());
            // También puedes añadir un mensaje flash de error si quieres informar al usuario
            // session()->flash('error', 'Hubo un problema al notificar a los recolectores.');
        }
    }









    public function confirmSchedule(PickupRequest $pickupRequest, FirebaseNotificationService $firebaseService)
    {
        $pickupRequest->update(['status' => 'programado']);

        try {
            // 2. Usamos la relación 'collector' que acabamos de crear
            $collector = $pickupRequest->collector;

            // 3. Verificamos que el recolector exista y tenga token
            if ($collector && $collector->fcm_token) {
                $title = "¡Horario Confirmado por el Cliente!";

                // Obtenemos el nombre del cliente para el mensaje
                $clientName = $pickupRequest->user->name ?? 'El cliente';
                $body = "{$clientName} ha aceptado tu propuesta de horario para la solicitud #{$pickupRequest->id}.";

                Log::debug("[ConfirmSchedule] Intentando notificar al RECOLECTOR [ID: {$collector->id}]...");

                // 4. Enviamos la notificación
                $success = $firebaseService->sendNotification($collector->fcm_token, $title, $body);

                if ($success) {
                    Log::debug("[ConfirmSchedule] -> Notificación enviada con éxito al recolector.");
                } else {
                    Log::warning("[ConfirmSchedule] -> Fallo al enviar notificación al recolector [ID: {$collector->id}].");
                }
            } else {
                Log::warning("[ConfirmSchedule] No se pudo notificar al recolector para la solicitud [ID: {$pickupRequest->id}]. Recolector no encontrado o sin token FCM.");
            }
        } catch (Exception $e) {
            Log::error('[ConfirmSchedule] Error al notificar horario confirmado al recolector: ' . $e->getMessage(), ['exception' => $e]);
        }
        // --- FIN DE LA NOTIFICACIÓN ---

        return redirect()->route('dashboard')->with('success', '¡Horario confirmado! Tu recojo está programado.');
    }

    /**
     * El cliente rechaza el horario propuesto.
     */
    public function rejectSchedule(PickupRequest $pickupRequest, FirebaseNotificationService $firebaseService)
    {
        // --- LÓGICA DE NOTIFICACIÓN (ANTES DE ACTUALIZAR) ---
        try {
            // 1. Buscamos al recolector
            $collector = $pickupRequest->collector;

            // 2. ¡¡ESTE ES EL SEGURO IMPORTANTE!!
            // Comprueba que el recolector exista Y que su token NO SEA NULO.
            if ($collector && $collector->fcm_token) {
                // Si SÍ tiene token, intentamos notificar
                $title = "Horario Rechazado por el Cliente";
                $clientName = $pickupRequest->user->name ?? 'El cliente';
                $body = "{$clientName} ha rechazado tu propuesta de horario. La solicitud #{$pickupRequest->id} vuelve a estar 'pendiente'.";

                Log::debug("[RejectSchedule] Intentando notificar al RECOLECTOR [ID: {$collector->id}]...");

                // Esta línea ahora es segura, nunca recibirá un 'null'
                $success = $firebaseService->sendNotification($collector->fcm_token, $title, $body);

                if ($success) {
                    Log::debug("[RejectSchedule] -> Notificación enviada con éxito al recolector.");
                } else {
                    Log::warning("[RejectSchedule] -> Fallo al enviar notificación al recolector [ID: {$collector->id}].");
                }
            } else {
                // Si el recolector no existe o no tiene token, solo lo logueamos y no crashea
                Log::warning("[RejectSchedule] No se pudo notificar al recolector para [ID: {$pickupRequest->id}]. Recolector no encontrado o sin token FCM.");
            }
        } catch (Exception $e) {
            Log::error('[RejectSchedule] Error al notificar horario rechazado al recolector: ' . $e->getMessage(), ['exception' => $e]);
        }
        // --- FIN DE LA NOTIFICACIÓN ---


        // 3. Actualizamos la solicitud (tu código original)
        // Esto se ejecuta después de notificar, ya sea que se haya enviado o no
        $pickupRequest->update([
            'status' => 'pendiente',
            'collector_id' => null,
            'proposed_date' => null,
            'proposed_time_start' => null,
            'proposed_time_end' => null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Has rechazado el horario. La solicitud está disponible nuevamente.');
    }
}
