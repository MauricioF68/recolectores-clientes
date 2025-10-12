<?php

namespace App\Http\Controllers\Recolector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return "<h1>Bienvenido al Panel de Recolector</h1>";
    }
}