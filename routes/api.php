<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Example endpoint
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/create', [UserController::class, 'create']);
    Route::post('/', [UserController::class, 'store']);
});
