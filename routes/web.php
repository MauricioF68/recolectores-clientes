<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/recolectores', [\App\Http\Controllers\Admin\CollectorController::class, 'index'])->name('admin.recolectores.index');
    
    Route::post('/admin/recolectores', [\App\Http\Controllers\Admin\CollectorController::class, 'store'])->name('admin.recolectores.store');
     
    Route::get('/admin/recolectores/{collector}/edit', [\App\Http\Controllers\Admin\CollectorController::class, 'edit'])->name('admin.recolectores.edit');
    
    Route::put('/admin/recolectores/{collector}', [\App\Http\Controllers\Admin\CollectorController::class, 'update'])->name('admin.recolectores.update');
    
    Route::delete('/admin/recolectores/{collector}', [\App\Http\Controllers\Admin\CollectorController::class, 'destroy'])->name('admin.recolectores.destroy');
});


Route::middleware(['auth'])->group(function() {
    // PASO 1: Muestra el mapa de ubicación
    Route::get('/solicitar-recojo/paso-1', [\App\Http\Controllers\Client\PickupRequestController::class, 'createStepOne'])->name('client.request.step-one.create');
    
    // PASO 1: Procesa y guarda temporalmente la ubicación
    Route::post('/solicitar-recojo/paso-1', [\App\Http\Controllers\Client\PickupRequestController::class, 'postStepOne'])->name('client.request.step-one.post');

    Route::get('/solicitar-recojo/paso-2', [\App\Http\Controllers\Client\PickupRequestController::class, 'createStepTwo'])->name('client.request.step-two.create');
    
    Route::post('/solicitar-recojo/paso-2', [\App\Http\Controllers\Client\PickupRequestController::class, 'store'])->name('client.request.step-two.store');
});


// Ruta para mostrar el formulario de registro de recolectores
Route::get('/registro/recolector', [\App\Http\Controllers\Auth\CollectorRegistrationController::class, 'create'])->name('collector.register');

Route::post('/registro/recolector', [\App\Http\Controllers\Auth\CollectorRegistrationController::class, 'verify'])->name('collector.register.verify');

// Muestra el formulario final de registro (con contraseña)
Route::get('/registro/recolector/completar/{dni}', [\App\Http\Controllers\Auth\CollectorRegistrationController::class, 'showRegistrationForm'])->name('collector.register.form');

// RUTA PARA CREAR EL USUARIO FINAL (NUEVA)
Route::post('/registro/recolector/completar', [\App\Http\Controllers\Auth\CollectorRegistrationController::class, 'store'])->name('collector.register.store');

// Ruta para el panel del recolector
Route::get('/recolector/dashboard', [\App\Http\Controllers\Recolector\DashboardController::class, 'index'])
    ->middleware(['auth']) // Aseguramos que solo usuarios logueados puedan entrar
    ->name('recolector.dashboard');


require __DIR__ . '/auth.php';
