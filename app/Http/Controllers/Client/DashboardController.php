<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user(); 
        
        $requests = $user->pickupRequests()
                         ->with('collector') // Esto carga al recolector asociado
                         ->latest()
                         ->get();

        return view('dashboard', ['requests' => $requests]);
    }
}