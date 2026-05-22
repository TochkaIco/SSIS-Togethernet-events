<x-mail::message>
# {{ __('Updated Terms of Service') }}

{{ __('Hello :name,', ['name' => $user->name]) }}

{{ __('We have updated our Terms of Service to better reflect our current features and data privacy practices.') }}

**{{ __('What you need to do:') }}**
{{ __('Please log in to TogethernetEvents and review the new terms. You will be prompted to accept them upon your next login.') }}

**{{ __('Important Deadline:') }}**
{{ __('You must accept the new terms within **one month** from today.') }}

**{{ __('What happens if I don\'t accept?') }}**
{{ __('If the new terms are not accepted by **:date**, your account will be automatically anonymized for privacy and security reasons.', ['date' => now()->addMonth()->format('Y-m-d')]) }}

<x-mail::button :url="config('app.url') . '/login'">
{{ __('Review and Accept Terms') }}
</x-mail::button>

{{ __('With best regards,') }}<br>
Togethernet
</x-mail::message>
