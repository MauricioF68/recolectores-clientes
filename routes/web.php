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

Route::get('/dashboard', [App\Http\Controllers\Client\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// --- RUTAS DEL ADMIN ---
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/recolectores', [\App\Http\Controllers\Admin\CollectorController::class, 'index'])->name('admin.recolectores.index');
    
    Route::post('/admin/recolectores', [\App\Http\Controllers\Admin\CollectorController::class, 'store'])->name('admin.recolectores.store');
     
    Route::get('/admin/recolectores/{collector}/edit', [\App\Http\Controllers\Admin\CollectorController::class, 'edit'])->name('admin.recolectores.edit');
    
    Route::put('/admin/recolectores/{collector}', [\App\Http\Controllers\Admin\CollectorController::class, 'update'])->name('admin.recolectores.update');
    
    Route::delete('/admin/recolectores/{collector}', [\App\Http\Controllers\Admin\CollectorController::class, 'destroy'])->name('admin.recolectores.destroy');

    Route::resource('/admin/rewards', \App\Http\Controllers\Admin\RewardController::class)->names('admin.rewards');

    Route::get('/admin/reward-claims', [\App\Http\Controllers\Admin\RewardClaimController::class, 'index'])->name('admin.claims.index');

    Route::patch('/admin/reward-claims/{claim}/fulfill', [\App\Http\Controllers\Admin\RewardClaimController::class, 'fulfill'])->name('admin.claims.fulfill');
});

// --- RUTAS DEL CLIENTES ---
Route::middleware(['auth'])->group(function() {
    // PASO 1: Muestra el mapa de ubicación
    Route::get('/solicitar-recojo/paso-1', [\App\Http\Controllers\Client\PickupRequestController::class, 'createStepOne'])->name('client.request.step-one.create');
    
    // PASO 1: Procesa y guarda temporalmente la ubicación
    Route::post('/solicitar-recojo/paso-1', [\App\Http\Controllers\Client\PickupRequestController::class, 'postStepOne'])->name('client.request.step-one.post');

    Route::get('/solicitar-recojo/paso-2', [\App\Http\Controllers\Client\PickupRequestController::class, 'createStepTwo'])->name('client.request.step-two.create');
    
    Route::post('/solicitar-recojo/paso-2', [\App\Http\Controllers\Client\PickupRequestController::class, 'store'])->name('client.request.step-two.store');

    Route::patch('/solicitud/{pickupRequest}/confirmar', [\App\Http\Controllers\Client\PickupRequestController::class, 'confirmSchedule'])->name('client.request.confirm');
    
    Route::patch('/solicitud/{pickupRequest}/rechazar', [\App\Http\Controllers\Client\PickupRequestController::class, 'rejectSchedule'])->name('client.request.reject');

    Route::get('/recompensas', [\App\Http\Controllers\Client\RewardController::class, 'index'])->name('client.rewards.index');

    Route::post('/recompensas/{reward}/canjear', [\App\Http\Controllers\Client\RewardController::class, 'redeem'])->name('client.rewards.redeem');
    
});

// --- RUTAS DEL RECOLECTOR ---
Route::middleware(['auth'])->group(function() { // Más adelante, cambiaremos 'auth' por un middleware de recolector
    Route::get('/recolector/dashboard', [\App\Http\Controllers\Recolector\DashboardController::class, 'index'])->name('recolector.dashboard');    
    // Ruta para ver los detalles de una solicitud
    Route::get('/recolector/solicitud/{pickupRequest}', [\App\Http\Controllers\Recolector\PickupRequestController::class, 'show'])->name('recolector.request.show');

    Route::patch('/recolector/solicitud/{pickupRequest}/aceptar', [\App\Http\Controllers\Recolector\PickupRequestController::class, 'accept'])->name('recolector.request.accept');

    Route::get('/recolector/solicitud/{pickupRequest}/programar', [\App\Http\Controllers\Recolector\PickupRequestController::class, 'showScheduleForm'])->name('recolector.request.schedule.show');
    
    Route::get('/recolector/mis-recojos', [\App\Http\Controllers\Recolector\DashboardController::class, 'myPickups'])->name('recolector.my-pickups');

    Route::post('/recolector/solicitud/{pickupRequest}/programar', [\App\Http\Controllers\Recolector\PickupRequestController::class, 'storeSchedule'])->name('recolector.request.schedule.store');

    
    Route::patch('/recolector/solicitud/{pickupRequest}/en-camino', [\App\Http\Controllers\Recolector\PickupRequestController::class, 'setInProgress'])->name('recolector.request.in-progress');

    
    Route::patch('/recolector/solicitud/{pickupRequest}/completado', [\App\Http\Controllers\Recolector\PickupRequestController::class, 'setCompleted'])->name('recolector.request.completed');
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
