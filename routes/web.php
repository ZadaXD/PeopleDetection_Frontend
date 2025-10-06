<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CameraController;

Auth::routes();
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/events', [DashboardController::class, 'getEvents'])->name('dashboard.events');
    Route::get('/dashboard/people', [DashboardController::class, 'getPeopleCount'])->name('dashboard.people');
    Route::get('/dashboard/search', [DashboardController::class, 'search'])->name('dashboard.search');

    // Kamera
    Route::post('/cameras/store', [CameraController::class, 'storeToDb'])->name('cameras.storeDb');
    Route::delete('/cameras/{id}', [CameraController::class, 'deleteCamera'])->name('cameras.destroy');

    Route::post('/cameras/{id}/start', [CameraController::class, 'startRecording'])->name('cameras.start');
    Route::get('/cameras/{id}/edit-zone', [CameraController::class, 'editZone'])->name('cameras.editZone');
    Route::post('/cameras/{id}/zone', [CameraController::class, 'storeZone'])->name('cameras.storeZone');
    Route::delete('/cameras/{id}/zones/{zoneId}', [CameraController::class, 'deleteZone'])->name('cameras.deleteZone');
    Route::post('/cameras/{id}/min-session', [CameraController::class, 'setMinSession'])->name('cameras.setMinSession');
});
