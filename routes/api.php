<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\MeetingApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\TeamApiController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Session-based (Blade) auth + token-based (Sanctum) auth for the React SPA.
*/

// ---- Blade (session) auth, kept for the legacy server-rendered app ----
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ---- React SPA (token-based, Laravel Sanctum) ----
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::post('/auth/profile', [AuthApiController::class, 'updateProfile']);

    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // Meetings CRUD + join by code
    Route::apiResource('meetings', MeetingApiController::class)->parameters(['meetings' => 'meeting']);
    Route::post('/meetings/{meeting}/end', [MeetingApiController::class, 'end']);
    Route::post('/join/{code}', [MeetingApiController::class, 'joinByCode']);

    // Tasks CRUD
    Route::apiResource('tasks', TaskApiController::class);

    // Teams CRUD + membership
    Route::apiResource('teams', TeamApiController::class);
    Route::post('/teams/{team}/members', [TeamApiController::class, 'addMember']);
    Route::delete('/teams/{team}/members/{userId}', [TeamApiController::class, 'removeMember']);

    // Notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/mark-as-read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationApiController::class, 'markAllAsRead']);

    // AI Features (API only)
    Route::post('/meetings/{meeting}/transcribe', [AIController::class, 'transcribe']);
    Route::post('/meetings/{meeting}/summarize', [AIController::class, 'summarize']);
    Route::post('/meetings/{meeting}/action-items', [AIController::class, 'extractActionItems']);
});
