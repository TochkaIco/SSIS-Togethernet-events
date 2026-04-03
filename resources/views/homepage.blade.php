<x-layouts::app title="Home">
    <div class="flex mt-12 items-center justify-center w-full">
        <div class="w-full max-w-4xl px-4 lg:px-0">
            <flux:card class="overflow-hidden p-0 mb-8 border-none bg-zinc-50 dark:bg-zinc-900 shadow-lg">
                <img src="{{ asset('/images/togethernet-feature.jpg') }}" alt="Image" class="w-full h-64 object-cover">
                <div class="p-8">
                    <flux:heading size="xl" class="mb-2">{{ __('Welcome to Togethernet') }}</flux:heading>
                    <flux:text size="lg" class="mb-6">
                        {{ __('We are an organization dedicated to bringing our school community together through karaoke, film nights, and various other social events. Join us for a night to remember!') }}
                    </flux:text>
                    <div class="flex gap-4">
                        @auth
                            <flux:button variant="primary" href="{{ route('events') }}" icon="layout-grid">Browse Events</flux:button>
                        @else
                            <flux:button variant="primary" href="{{ route('login') }}" icon="user-plus">Join Us</flux:button>
                        @endauth
                    </div>
                </div>
            </flux:card>

            <flux:separator class="my-12 w-full" />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <flux:card class="flex flex-col">
                    <flux:heading size="lg" icon="musical-note">Karaoke</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __('Sing your heart out at our weekly karaoke sessions. No talent required, just enthusiasm!') }}
                    </flux:text>
                </flux:card>

                <flux:card class="flex flex-col">
                    <flux:heading size="lg" icon="film">{{ __('Film Nights') }}</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __('Relax and enjoy classic films and latest blockbusters on our big screen with fresh popcorn.') }}
                    </flux:text>
                </flux:card>

                <flux:card class="flex flex-col">
                    <flux:heading size="lg" icon="sparkles">LAN</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __("From game tournaments to themed parties, there're always things happening at our LAN-events.") }}
                    </flux:text>
                    <flux:button href="https://lan.ssis.nu/" target="_blank" variant="ghost" size="sm" class="mt-4 self-start" icon="link">Learn more</flux:button>
                </flux:card>

                <flux:card class="flex flex-col">
                    <flux:heading size="lg" icon="sparkles">QR-Tag</flux:heading>
                    <flux:text class="mt-2 grow">
                        {{ __('QRTag is a digital version of the "Killer Game" and a great way to meet other students at school.') }}
                    </flux:text>
                    <flux:button href="https://qrtag.ssis.nu/" target="_blank" variant="ghost" size="sm" class="mt-4 self-start" icon="link">Learn more</flux:button>
                </flux:card>
            </div>
        </div>
    </div>
    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif
</x-layouts::app>
