@component('mail::message')
# Hello,

You have been invited to join a team **{{ $invitation->team->name}}** on
[DesignHouse.com]({{ config('app.client_url') }}).
To join the team, please:

@component('mail::button', ['url' => $url])
Register for free
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
