<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // AI Features (API only)
    Route::post('/meetings/{meeting}/transcribe', [AIController::class, 'transcribe']);
    Route::post('/meetings/{meeting}/summarize', [AIController::class, 'summarize']);
    Route::post('/meetings/{meeting}/action-items', [AIController::class, 'extractActionItems']);
});