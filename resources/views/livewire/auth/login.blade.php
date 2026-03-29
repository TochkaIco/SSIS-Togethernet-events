<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-center">
            <x-svg.app-logo.text.light class="hidden dark:block" />
            <x-svg.app-logo.text.dark class="block dark:hidden" />
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <div class="flex flex-col gap-3 items-center">
            <flux:description>
                {{ __('Only Google OAuth logins are supported.') }}
            </flux:description>
            <flux:button type="button" variant="primary" href="{{ route('auth.google') }}" class="w-full h-16" data-test="google-login-button">
                {{ __('Log in via Google') }}
            </flux:button>
            <flux:separator />
            <flux:button type="button" :href="route('home')" class="w-full" data-test="back-button">
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>
</x-layouts::auth>
