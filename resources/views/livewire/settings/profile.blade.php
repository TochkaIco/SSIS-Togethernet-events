<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout wire:key="settings-layout" :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            @if(auth()->user()->profile_picture)
                <flux:avatar :src="auth()->user()->profile_picture" :name="auth()->user()->name" :initials="auth()->user()->initials()" class="h-24 w-24 mb-2" circle />
                <flux:text class="mt-1 text-xs text-orange-400/80 cursor-default">{{ __('Your profile picture is handled by Google.') }}</flux:text>
                <flux:button wire:click="pullPictureFromGoogle()" class="cursor-pointer" icon="arrow-path" variant="ghost">Pull the picture from Google</flux:button>
            @else
                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" class="h-24 w-24 mb-2" circle />
            @endif

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                @if($this->google_id !== '')
                    <flux:input wire:model="email" :label="__('Email')" disabled />
                    <flux:text class="mt-1 text-xs text-orange-400/80 cursor-default">{{ __('Your email is handled by Google.') }}</flux:text>
                @else
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />
                @endif

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
