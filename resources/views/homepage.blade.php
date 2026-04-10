<x-layouts::app :title="__('Home')">
    <div class="flex mt-12 items-center justify-center w-full">
        <div class="w-full max-w-4xl px-4 lg:px-0">
            <flux:card class="transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                <img src="{{ asset('/images/togethernet-feature.jpg') }}" alt="Image" class="w-full h-64 object-cover rounded-t-xl">
                <div class="p-8">
                    <flux:heading size="xl" class="mb-2 flex flex-wrap items-center gap-x-2 text-3xl lg:col-span-2">
                        <span>{{ __('Welcome to') }}</span>
                        <x-svg.app-logo.text.light class="hidden h-[1em] translate-y-0.75 w-auto dark:block" />
                        <x-svg.app-logo.text.dark class="h-[1em] w-auto translate-y-0.75 dark:hidden" />
                    </flux:heading>
                    <flux:text size="lg" class="mb-6">
                        {{ __('Togethernet is a 13-year-old organization founded and run by committed students at Stockholm Science & Innovation School to create fun events for all students.') }}
                    </flux:text>
                    <div class="flex gap-4">
                        @auth
                            <flux:button variant="primary" :href="route('events')" icon="layout-grid">{{ __('Browse Events') }}</flux:button>
                        @else
                            <flux:button variant="primary" :href="route('login')" icon="user-plus">{{ __('Join Us') }}</flux:button>
                        @endauth
                    </div>
                </div>
            </flux:card>

            <flux:separator class="my-12 w-full" />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <flux:card class="flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                    <flux:heading size="lg" icon="musical-note">{{ __('Karaoke') }}</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __('Sing your heart out at our karaoke sessions. No talent required, just enthusiasm!') }}
                    </flux:text>
                </flux:card>

                <flux:card class="flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                    <flux:heading size="lg" icon="film">{{ __('Film Nights') }}</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __('Relax and enjoy a film session with your fellow students.') }}
                    </flux:text>
                </flux:card>

                <flux:card class="flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                    <flux:heading size="lg" icon="sparkles">{{ __('LAN') }}</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __("From game tournaments to themed parties, there're always things happening at our LAN-events.") }}
                    </flux:text>
                    <flux:button href="https://lan.ssis.nu/" target="_blank" variant="ghost" size="sm" class="mt-4 self-start" icon="link">{{ __('Learn more') }}</flux:button>
                </flux:card>

                <flux:card class="flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                    <flux:heading size="lg" icon="sparkles">{{ __('QR-Tag') }}</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __('QRTag is a digital version of the "Killer Game" and a great way to meet other students at school.') }}
                    </flux:text>
                    <flux:button href="https://qrtag.ssis.nu/" target="_blank" variant="ghost" size="sm" class="mt-4 self-start" icon="link">{{ __('Learn more') }}</flux:button>
                </flux:card>
            </div>
        </div>
    </div>
    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif
</x-layouts::app>
