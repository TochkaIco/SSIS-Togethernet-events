<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-center">
            <x-svg.app-logo.text.light class="hidden dark:block" />
            <x-svg.app-logo.text.dark class="block dark:hidden" />
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <div class="flex flex-col gap-3 items-center">
            @if(App\Models\AppConfig::get('active_auth_provider', 'google') === 'google')
                <flux:description>
                    {{ __('Only Google OAuth logins are supported.') }}
                </flux:description>
            @endif
            <flux:button type="button" variant="primary" href="{{ route('auth.login') }}" class="w-full h-16" data-test="google-login-button">
                @if(App\Models\AppConfig::get('active_auth_provider', 'google') === 'google')
                    {{ __('Log in via Google') }}
                @else
                    {{ __('Log in') }}
                @endif
            </flux:button>
            <flux:separator />
            <flux:button type="button" :href="route('home')" class="w-full" data-test="back-button">
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>

    <flux:toast />

    @if (session('error'))
        <script>
            // Flux provides a global 'Flux' object in the browser
            window.addEventListener('DOMContentLoaded', () => {
                Flux.toast({
                    variant: 'danger',
                    heading: 'Access Denied',
                    text: "{{ session('error') }}"
                });
            });
        </script>
    @endif
</x-layouts::auth>
