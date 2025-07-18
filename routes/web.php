<?php

use Illuminate\Support\Facades\Route;

Route::get('/api/{any}', function () {
    return redirect('/api');
})->where('any', '.*');

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel работает!',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()
    ]);
});
