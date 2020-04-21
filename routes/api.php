<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to Design House API'
    ], 200);
});

// Route group for authenticated users
Route::group(['middleware' => ['auth:api']], function () {
});

// Route group for guest users
Route::group(['middleware' => ['guest:api']], function () {
    Route::post('/register',  'Auth\RegisterController@register');
});
