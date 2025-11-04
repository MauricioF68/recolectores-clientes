<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CollectorMasterList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CollectorRegistrationController extends Controller
{
    /**
     * Muestra el primer paso del formulario de registro del recolector (verificación de DNI).
     */
    public function create()
    {
        return view('auth.collector-register');
    }
    
    public function store(Request $request)
    {
        // 1. Validamos los datos del formulario final
        $request->validate([
            'dni' => 'required|string|exists:collectors_master_list,dni',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|digits:9|unique:users,phone',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Buscamos los datos del recolector en la lista maestra
        $collectorData = CollectorMasterList::where('dni', $request->dni)->first();

        // 3. Creamos el nuevo usuario en la tabla 'users'
        $user = User::create([
            'name' => $collectorData->first_name . ' ' . $collectorData->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'recolector',
            'dni' => $request->dni,
        ]);

        // 4. Iniciamos sesión con el nuevo usuario
        Auth::login($user);

        // 5. Redirigimos al recolector a su propio panel (que crearemos a continuación)
        return redirect('/recolector/dashboard');
    }



    public function verify(Request $request)
    {
        // 1. Valida que el campo DNI no esté vacío.
        $request->validate([
            'dni' => 'required|string|digits:8', // Asumimos DNI de 8 dígitos
        ]);

        // 2. Busca al recolector en la lista maestra.
        $collector = CollectorMasterList::where('dni', $request->dni)
            ->where('status', 'activo')
            ->first();

        // 3. Comprueba el resultado de la búsqueda.
        if ($collector) {
            // Caso Éxito: ¡El recolector existe y está activo!
            // Por ahora, solo devolveremos un mensaje de éxito.
            // En el siguiente paso, lo redirigiremos al formulario de registro completo.
            return redirect()->route('collector.register.form', ['dni' => $collector->dni]);
        } else {
            // Caso Fallo: No se encontró o no está activo.
            // Regresamos a la página anterior con un mensaje de error.
            return back()->withErrors([
                'dni' => 'Este DNI no se encuentra registrado o no está autorizado.',
            ]);
        }
    }

    public function showRegistrationForm($dni)
    {
        // Buscamos de nuevo los datos del recolector para pasarlos a la vista.
        $collectorData = CollectorMasterList::where('dni', $dni)->firstOrFail();

        // Devolvemos la vista del formulario final (que crearemos a continuación).
        return view('auth.collector-register-final', compact('collectorData'));
    }
}
