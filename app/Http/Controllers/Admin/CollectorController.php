<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CollectorMasterList; // 1. Importamos el Modelo

class CollectorController extends Controller
{
    // 2. Creamos la función para mostrar la lista
    public function index()
    {
        // 3. Obtenemos todos los recolectores de la base de datos
        $collectors = CollectorMasterList::all();

        // 4. Devolvemos la vista y le pasamos la lista de recolectores
        return view('admin.recolectores.index', compact('collectors'));
    }

    // ... (Aquí está tu función index() que ya creamos) ...

    /**
     * Guarda un nuevo recolector en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'second_last_name' => 'nullable|string|max:255',
            'dni' => 'required|string|unique:collectors_master_list,dni', // DNI es requerido y debe ser único en la tabla
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'department' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
        ]);

        // 2. Crear el nuevo recolector con los datos validados
        CollectorMasterList::create($validatedData);

        // 3. Redirigir de vuelta a la lista con un mensaje de éxito
        return redirect()->route('admin.recolectores.index')->with('success', 'Recolector añadido correctamente.');
    }

    public function edit(CollectorMasterList $collector)
    {
        return response()->json($collector);
    }

    public function update(Request $request, CollectorMasterList $collector)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => [
                'required',
                'string',
                Rule::unique('collectors_master_list')->ignore($collector->id), // Valida DNI único, ignorando el actual
            ],
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:activo,suspendido', // Nos aseguramos que el estado sea uno de los dos valores
            // ... (puedes añadir el resto de campos si también quieres que se puedan editar)
        ]);

        $collector->update($validatedData);

        return redirect()->route('admin.recolectores.index')->with('success', 'Recolector actualizado correctamente.');
    }

    public function destroy(CollectorMasterList $collector)
    {
        $collector->delete();

        return redirect()->route('admin.recolectores.index')->with('success', 'Recolector eliminado correctamente.');
    }
}
