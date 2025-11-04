<?php

namespace App\Http\Controllers\Recolector;

use App\Http\Controllers\Controller;
use App\Models\CollectorMasterList;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pendingRequests = collect(); // Por defecto, una colección vacía

        $scheduledRequests = PickupRequest::where('collector_id', $user->id)
            ->whereIn('status', ['aceptado', 'programado']) // Aceptadas o ya programadas
            ->orderBy('proposed_date', 'asc')
            ->orderBy('proposed_time_start', 'asc')
            ->get();

        // 1. Buscamos el registro del recolector en la lista maestra para obtener su ubicación
        $collector_record = CollectorMasterList::where('dni', $user->dni)->first();

        // 2. Si el recolector tiene coordenadas, buscamos las solicitudes cercanas
        if ($collector_record && $collector_record->latitude && $collector_record->longitude) {
            $collectorLat = $collector_record->latitude;
            $collectorLng = $collector_record->longitude;

            // Fórmula Haversine para calcular distancia en Km
            $distanceQuery = DB::raw("
                ( 6371 * acos( cos( radians(?) ) *
                  cos( radians( latitude ) )
                  * cos( radians( longitude ) - radians(?)
                  ) + sin( radians(?) ) *
                  sin( radians( latitude ) ) )
                ) AS distance
            ");

            $pendingRequests = PickupRequest::select('*')
                ->addSelect($distanceQuery)
                ->setBindings([$collectorLat, $collectorLng, $collectorLat])
                ->where('status', 'pendiente') // 3. Filtramos solo las pendientes
                ->orderBy('distance', 'asc')    // 4. Ordenamos por distancia
                ->get();
        }

        // 5. Devolvemos la vista y le pasamos la lista de solicitudes
        return view('recolector.dashboard', [
            'requests' => $pendingRequests,
        ]);
    }

    public function myPickups()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $scheduledRequests = PickupRequest::with('user')
            ->where('collector_id', $user->id)
            ->whereIn('status', ['aceptado', 'programado', 'en_camino'])
            ->orderBy('proposed_date', 'asc')
            ->orderBy('proposed_time_start', 'asc')
            ->get();

        return view('recolector.my-pickups', [
            'requests' => $scheduledRequests,
        ]);
    }
}