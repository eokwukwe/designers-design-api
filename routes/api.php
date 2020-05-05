<?php

use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('me', 'User\MeController@getMe');
Route::get('users', 'User\UserController@index');

// Get designs
Route::get('designs', 'Designs\DesignController@index');
Route::get('designs/{id}', 'Designs\DesignController@findDesign');

// Teams
Route::get('teams/slug/{slug}', 'Teams\TeamsController@findBySlug');

// Search Designs
Route::get('search/designs', 'Designs\DesignController@search');



// Route group for authenticated users
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', 'Auth\LoginController@logout');
    Route::put('settings/profile',  'User\SettingsController@updateProfile');
    Route::put('settings/password',  'User\SettingsController@updatePassword');

    // Upload Designs
    Route::post('designs', 'Designs\UploadController@upload');
    Route::put('designs/{id}', 'Designs\DesignController@update');

    Route::delete('designs/{id}', 'Designs\DesignController@destroy');

    // Comments
    Route::post('designs/{id}/comments', 'Designs\CommentController@store');
    Route::put('comments/{id}', 'Designs\CommentController@update');
    Route::delete('comments/{id}', 'Designs\CommentController@destroy');

    // Likes and Unlikes
    Route::post('designs/{id}/likes', 'Designs\DesignController@like');
    Route::get(
        'designs/{id}/liked',
        'Designs\DesignController@checkIfUserHasLiked'
    );

    // Teams
    Route::post('teams', 'Teams\TeamsController@store');
    Route::get('teams/{id}', 'Teams\TeamsController@findById');
    Route::get('teams', 'Teams\TeamsController@index');
    Route::get('users/teams', 'Teams\TeamsController@getUserTeams');
    Route::put('teams/{id}', 'Teams\TeamsController@update');
    Route::delete('teams/{id}', 'Teams\TeamsController@destroy');
    Route::delete(
        'teams/{teamId}/users/{userId}',
        'Teams\TeamsController@removeFromTeam'
    );

    // Invitation
    Route::post('invitations/{teamId}', 'Teams\InvitationsController@invite');
    Route::post(
        'invitations/{id}/resend',
        'Teams\InvitationsController@resend'
    );
    Route::post(
        'invitations/{id}/respond',
        'Teams\InvitationsController@respond'
    );
    Route::delete(
        'invitations/{id}',
        'Teams\InvitationsController@destroy'
    );

    // Chats
    Route::post('chats', 'Chats\ChatsController@sendMessage');
    Route::get('chats', 'Chats\ChatsController@getUserChats');
    Route::get('chats/{id}/messages', 'Chats\ChatsController@getChatMessages');
    Route::put('chats/{id}/read', 'Chats\ChatsController@markAsRead');
    Route::delete('messages/{id}', 'Chats\ChatsController@destroyMessage');
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
    Route::post(
        'password/email',
        'Auth\ForgotPasswordController@sendResetLinkEmail'
    );
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});
