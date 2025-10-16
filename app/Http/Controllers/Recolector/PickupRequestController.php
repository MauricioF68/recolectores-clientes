<?php

namespace App\Http\Controllers\Recolector;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\User;
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
        return redirect()->route('recolector.request.schedule.show', $pickupRequest);
    }

    public function showScheduleForm(PickupRequest $pickupRequest)
    {
        return view('recolector.requests.schedule', ['request' => $pickupRequest]);
    }

    public function storeSchedule(Request $request, PickupRequest $pickupRequest)
    {
        $validatedData = $request->validate([
            'proposed_date' => 'required|date|after_or_equal:today',
            'proposed_time_start' => 'required|date_format:H:i',
            'proposed_time_end' => 'required|date_format:H:i|after:proposed_time_start',
        ]);

        $pickupRequest->update($validatedData);

        return redirect()->route('recolector.dashboard')->with('success', 'Horario propuesto enviado al cliente.');
    }






    public function setInProgress(PickupRequest $pickupRequest)
    {
        if ($pickupRequest->collector_id !== Auth::id()) {
            abort(403);
        }
        $pickupRequest->update(['status' => 'en_camino']);
        return back()->with('success', 'El cliente ha sido notificado que estás en camino.');
    }
    
    public function setCompleted(PickupRequest $pickupRequest)
    {
        if ($pickupRequest->collector_id !== Auth::id()) {
            abort(403);
        }

        $pickupRequest->update(['status' => 'completado']);

        
        $client = $pickupRequest->user;
        if ($client) {
            $client->points_balance += 10; // Asignamos 10 puntos (puedes cambiar este valor)
            $client->save();
        }

        return redirect()->route('recolector.my-pickups')->with('success', '¡Recojo completado exitosamente!');
    }
}
