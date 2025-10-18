<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rewards = \App\Models\Reward::all();
        return view('admin.rewards.index', ['rewards' => $rewards]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rewards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'points_cost' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rewards', 'public');
        }

        \App\Models\Reward::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'points_cost' => $validatedData['points_cost'],
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.rewards.index')->with('success', 'Recompensa creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reward $reward)
    {
        return response()->json($reward);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reward $reward)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'points_cost' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = $reward->image_path; // Mantenemos la imagen anterior por defecto
        if ($request->hasFile('image')) {
            // Si se sube una nueva imagen, borramos la antigua si existe
            if ($reward->image_path) {
                Storage::disk('public')->delete($reward->image_path);
            }
            // Guardamos la nueva imagen
            $imagePath = $request->file('image')->store('rewards', 'public');
        }

        $reward->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'points_cost' => $validatedData['points_cost'],
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.rewards.index')->with('success', 'Recompensa actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reward $reward)
    {
        // Si la recompensa tiene una imagen, la borramos del almacenamiento
        if ($reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        // Eliminamos el registro de la base de datos
        $reward->delete();

        return redirect()->route('admin.rewards.index')->with('success', 'Recompensa eliminada correctamente.');
    }
}
