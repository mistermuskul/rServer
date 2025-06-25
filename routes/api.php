<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\GreenHouseController;
use App\Http\Controllers\HeroProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TelegramController;

Route::get('test', function () {
    try {
        DB::connection()->getPdo();
        
        return response()->json([
            'message' => 'API работает!',
            'timestamp' => now(),
            'status' => 'success',
            'route' => 'api/test',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'API работает, но есть проблема с БД',
            'error' => $e->getMessage(),
            'status' => 'warning'
        ], 500);
    }
});

Route::get('ping', function () {
    return response()->json([
        'message' => 'Pong!',
        'timestamp' => now(),
        'status' => 'success',
        'route' => 'api/ping'
    ]);
});

Route::post('auth/login', function (Request $request) {
    return response()->json([
        'message' => 'Auth endpoint работает!',
        'method' => $request->method(),
        'timestamp' => now()
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
            'ping' => '/api/ping',
            'auth' => '/api/auth/login',
            'hero' => '/api/hero'
        ],
        'timestamp' => now()
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hero/{id?}', [HeroProfileController::class, 'show']);
    Route::post('/hero', [HeroProfileController::class, 'store']);
    Route::put('/hero/{id?}', [HeroProfileController::class, 'update']);
    Route::apiResource('projects', ProjectController::class);
    
    Route::get('/messages/users', [MessageController::class, 'getUsers']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/unread-by-user', [MessageController::class, 'unreadCountByUser']);

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

