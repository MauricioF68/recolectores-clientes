<?php

namespace App\Http\Controllers\Recolector;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function accept(PickupRequest $pickupRequest)
    {
        // Actualizamos el estado y asignamos el recolector logueado
        $pickupRequest->update([
            'status' => 'aceptado',
            'collector_id' => Auth::id(),
        ]);

        // Redirigimos al panel con un mensaje de éxito
        return redirect()->route('recolector.dashboard')->with('success', '¡Has aceptado el recojo! Ahora puedes coordinar con el cliente.');
    }
}