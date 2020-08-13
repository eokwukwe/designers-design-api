<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Controller;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request, User $user)
    {
        // Check if the URL is a valid signed URL
        if (!URL::hasValidSignature($request)) {
            return response()->json([
                'error' => [
                    'message' => 'Invalid verification link'
                ]
            ], 403);
        }

        // Check if user has already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'error' => [
                    'message' => 'Email: ' . $user->email . ' is already verified'
                ]
            ], 422);
        }

        // Verify email
        $user->markEmailAsVerified();
        event(new Verified($user)); // Fire an event

        return response()->json([
            'message' => 'Email successfully verifed'
        ], 200);
    }

    public function resend(Request $request, User $user)
    {
        $this->validate($request, [
            'email' => ['email', 'required']
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => [
                    'message' => 'User with email: ' . $request->email . ' not found'
                ]
            ], 404);
        }

        // Check if user has already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'error' => [
                    'message' => 'Email: ' . $request->email . ' is already verified'
                ]
            ], 422);
        }

        // Resend verification notification
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link has been resent to email: ' . $request->email,
        ]);
    }
}
