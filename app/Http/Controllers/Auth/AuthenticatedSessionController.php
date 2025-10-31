<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        switch ($user->role) {
            case 'administrador':
                // Usamos el nombre de tu ruta de admin
                return redirect()->route('admin.dashboard');
                
            case 'recolector':
                // Usamos el nombre de tu ruta de recolector
                return redirect()->route('recolector.dashboard');
                
            case 'cliente': // O cualquier otro rol que no sea admin/recolector
            default:
                // Esta es la lógica original de Breeze
                return redirect()->intended(RouteServiceProvider::HOME); // HOME es '/dashboard'
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
