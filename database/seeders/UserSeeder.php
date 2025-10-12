<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Importamos el modelo User
use Illuminate\Support\Facades\Hash; // Importamos la utilidad para encriptar

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com', // Puedes cambiar este email
            'password' => Hash::make('admin123'), // ¡Cambia esto por una contraseña segura!
            'role' => 'administrador',
            'dni' => '00000000' // DNI de relleno para el admin
        ]);
    }
}