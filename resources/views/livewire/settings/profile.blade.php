<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout wire:key="settings-layout" :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            @if(auth()->user()->profile_picture)
                <flux:avatar :src="auth()->user()->profile_picture" :name="auth()->user()->name" :initials="auth()->user()->initials()" class="h-24 w-24 mb-2" circle />
                @if(App\Models\AppConfig::get('active_auth_provider', 'google') === 'google')
                    <flux:text class="mt-1 text-xs text-orange-400/80 cursor-default">{{ __('Your profile picture is handled by Google.') }}</flux:text>
                    <flux:button wire:click="pullPictureFromGoogle()" class="cursor-pointer" icon="arrow-path" variant="ghost">{{ __('Pull the picture from Google') }}</flux:button>
                @endif
            @else
                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" class="h-24 w-24 mb-2" circle />
            @endif

            <div>
                <flux:input wire:model="name" :label="__('Name')" type="text" disabled required autofocus autocomplete="name" />
            </div>

            <div>
                <flux:input wire:model="class" :label="__('Class')" type="text" disabled required />
            </div>

            <div>
                <flux:input wire:model="email" :label="__('Email')" disabled />
            </div>

            <flux:text class="mt-1 text-xs text-orange-400/80 cursor-default">{{ __('Your name, email and class are managed by Google / school\'s LDAP. Contact Togethernet to change your profile information in the system.') }}</flux:text>
        </form>

        @auth
            <livewire:settings.delete-user-form />
        @endauth
    </x-settings.layout>
</section>
