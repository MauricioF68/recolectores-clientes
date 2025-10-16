<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
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

        // 3. Usamos una transacción para asegurar que todo se guarde correctamente
        DB::transaction(function () use ($stepOneData, $validatedStepTwo, $request) {

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

        // 5. Redirigimos con mensaje de éxito
        return redirect()->route('dashboard')->with('success', '¡Tu solicitud de recojo ha sido creada con éxito!');
    }

    public function confirmSchedule(PickupRequest $pickupRequest)
    {
        $pickupRequest->update(['status' => 'programado']);

        return redirect()->route('dashboard')->with('success', '¡Horario confirmado! Tu recojo está programado.');
    }

    /**
     * El cliente rechaza el horario propuesto.
     */
    public function rejectSchedule(PickupRequest $pickupRequest)
    {
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
