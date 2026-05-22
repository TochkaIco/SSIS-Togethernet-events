<x-mail::message>
# {{ __('Inactivity Warning') }}

{{ __('Hello :name,', ['name' => $user->name]) }}

{{ __("We noticed you haven't logged in for 6 months. To keep your account, please log in within one month.") }}

{{ __('Otherwise, your account will be anonymized after the following month for privacy reasons.') }}

<x-mail::button url="{{ config('app.url') }}/login">
{{ __('Login Now') }}
</x-mail::button>

{{ __('With best regards,') }}<br>
Togethernet
</x-mail::message>
