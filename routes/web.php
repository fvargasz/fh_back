<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/createUser', UserController::class);

// Route::middleware('api')->group(function () {
//     Route::prefix('users')->group(function () {
//         Route::get('/', [UserController::class, 'index']);
//         Route::post('/create', [UserController::class, 'create']);
//         Route::post('/', [UserController::class, 'store']);
//     });
// });