<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\TripController;

Route::prefix('users')->group(function () {
    Route::post('/create', [UserController::class, 'create']);
    Route::post('/login', [UserController::class, 'login']);
    
});

Route::prefix('flight')->group(function () {
    Route::post('/flights', [FlightController::class, 'getFlights']);
});

Route::prefix('airports')->group(function () {
    Route::post('/all', [AirportController::class, 'getAll']);
});

Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::post('/getActiveUser', [UserController::class, 'getActiveUser']);
});

Route::middleware('auth:sanctum')->prefix('trip')->group(function () {
    Route::post('/create', [TripController::class, 'createTrip']);
    Route::post('/getActiveUserTrips', [TripController::class, 'getUserTrips']);
});

