<?php

use Illuminate\Support\Facades\Route;

// Редирект с web на API для отладки
Route::get('/api/{any}', function () {
    return redirect('/api');
})->where('any', '.*');

Route::get('/', function () {
    return view('welcome');
});
