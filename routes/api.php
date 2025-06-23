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

// Тестовый маршрут для проверки API
Route::get('test', function () {
    try {
        // Проверка подключения к БД
        DB::connection()->getPdo();
        
        return response()->json([
            'message' => 'API работает!',
            'timestamp' => now(),
            'status' => 'success',
            'route' => 'api/test',
            'database' => 'connected',
            'connection' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'database_name' => config('database.connections.mysql.database')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'API работает, но есть проблема с БД',
            'error' => $e->getMessage(),
            'status' => 'warning',
            'connection' => config('database.default'),
            'host' => config('database.connections.mysql.host'),
            'database_name' => config('database.connections.mysql.database')
        ], 500);
    }
});

// Тестовый маршрут без префикса
Route::get('ping', function () {
    return response()->json([
        'message' => 'Pong!',
        'timestamp' => now(),
        'status' => 'success',
        'route' => 'api/ping'
    ]);
});

// Простой тест auth
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
        'timestamp' => now(),
        'database_connection' => config('database.default'),
        'database_host' => config('database.connections.mysql.host'),
        'database_name' => config('database.connections.mysql.database')
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hero/{id?}', [HeroProfileController::class, 'show']);
    Route::post('/hero', [HeroProfileController::class, 'store']);
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

// Тестовый маршрут для проверки переменных окружения БД
Route::get('db-config', function () {
    return response()->json([
        'db_connection' => env('DB_CONNECTION'),
        'db_host' => env('DB_HOST'),
        'db_port' => env('DB_PORT'),
        'db_database' => env('DB_DATABASE'),
        'db_username' => env('DB_USERNAME'),
        'db_password' => env('DB_PASSWORD') ? '***hidden***' : 'not_set',
        'db_url' => env('DB_URL') ? '***hidden***' : 'not_set',
        'mysql_host' => env('MYSQLHOST'),
        'mysql_port' => env('MYSQLPORT'),
        'mysql_database' => env('MYSQLDATABASE'),
        'mysql_user' => env('MYSQLUSER'),
        'mysql_password' => env('MYSQLPASSWORD') ? '***hidden***' : 'not_set',
        'mysql_url' => env('MYSQL_URL') ? '***hidden***' : 'not_set',
        'config_default' => config('database.default'),
        'config_host' => config('database.connections.mysql.host'),
        'config_database' => config('database.connections.mysql.database'),
        'config_username' => config('database.connections.mysql.username'),
    ]);
});

