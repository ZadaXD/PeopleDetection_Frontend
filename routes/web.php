<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CameraController;

Auth::routes();
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kamera
    Route::post('/cameras/store-db', [CameraController::class, 'storeToDb'])->name('cameras.storeDb');
    Route::delete('/cameras/{id}', [CameraController::class, 'destroy'])->name('cameras.destroy');
    Route::post('/cameras/{id}/start', [CameraController::class, 'startRecording'])->name('cameras.start');

    // Zona
    Route::get('/cameras/{id}/edit-zone', [CameraController::class, 'editZone'])->name('cameras.editZone');
    Route::post('/cameras/{id}/zones', [CameraController::class, 'storeZone'])->name('cameras.storeZone');
    Route::delete('/cameras/{id}/zones/{zoneId}', [CameraController::class, 'deleteZone'])->name('cameras.deleteZone');
    Route::post('/cameras/{id}/min-session', [CameraController::class, 'setMinSession'])->name('cameras.setMinSession');
});

