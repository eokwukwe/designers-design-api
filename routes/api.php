<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route group for authenticated users
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', 'Auth\LoginController@logout');
});

// Route group for guest users
Route::group(['middleware' => ['guest:api']], function () {
    Route::post('register',  'Auth\RegisterController@register');
    Route::post(
        'verification/verify/{user}',
        'Auth\VerificationController@verify'
    )->name('verification.verify');
    Route::post('verification/resend',  'Auth\VerificationController@resend');
    Route::post('login', 'Auth\LoginController@login');
});
