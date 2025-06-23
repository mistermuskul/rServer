<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\GreenHouseController;
use App\Http\Controllers\HeroProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TelegramController;

// Тестовый маршрут для проверки API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API работает!',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auto-login/{uuid}', [AuthController::class, 'autoLogin']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel API работает!',
        'endpoints' => [
            'test' => '/api/test',
            'auth' => '/api/auth/login',
            'hero' => '/api/hero'
        ]
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hero/{id?}', [HeroProfileController::class, 'show']);
    Route::put('/hero/{id?}', [HeroProfileController::class, 'update']);
    Route::apiResource('projects', ProjectController::class);
    
    // Message routes
    Route::get('/messages/users', [MessageController::class, 'getUsers']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/unread-by-user', [MessageController::class, 'unreadCountByUser']);

    // HR generation route
    Route::post('/user/generate-hr', [UserController::class, 'generateHR']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
            ]
        ]);
    });
});

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::post('/telegram/send', [TelegramController::class, 'sendToTelegram']);

