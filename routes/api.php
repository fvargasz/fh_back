<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\TripController;

// Example endpoint
Route::prefix('users')->group(function () {
    Route::post('/create', [UserController::class, 'create']);
    Route::post('/login', [UserController::class, 'login']);
    
});

Route::prefix('airports')->group(function () {
    Route::post('/all', [AirportController::class, 'getAll']);
});

// Authenticated routes can be added here
Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::post('/getActiveUser', [UserController::class, 'getActiveUser']);
});

Route::middleware('auth:sanctum')->prefix('flight')->group(function () {
    Route::post('/flights', [FlightController::class, 'getFlights']);
});

Route::middleware('auth:sanctum')->prefix('trip')->group(function () {
    Route::post('/create', [TripController::class, 'createTrip']);
    Route::post('/getActiveUserTrips', [TripController::class, 'getUserTrips']);
});

