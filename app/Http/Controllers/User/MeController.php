<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * The method gets the current logged in user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMe()
    {
        if (auth()->check()) {
            return new UserResource(auth()->user());
        }
        return response()->json(null, 401);
    }
}
