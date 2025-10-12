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

require __DIR__ . '/auth.php';
