<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail as NotificationsVerifyEmail;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends NotificationsVerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     * The URL will be for the frontend.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $appUrl = config('app.client_url', config('app.url'));

        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['user' => $notifiable->id]
        );

        // Return the URL that can be used in the front end
        return str_replace(url('/api'), $appUrl, $url);
    }
}
