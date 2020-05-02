@component('mail::message')
# Hello,

You have been invited to join the team **{{ $invitation->team->name}}** on
[DesignHouse.com]({{ config('app.client_url') }}).
To join the team, please:

@component('mail::button', ['url' => $url])
Log in
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent