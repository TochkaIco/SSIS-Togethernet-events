<flux:main class="flex min-h-screen flex-col transition-opacity opacity-100 duration-650 lg:grow starting:opacity-0">
    <div class="fixed inset-0 bg-zinc-950 text-zinc-100 flex flex-col items-center justify-center p-8 gap-8" wire:poll.60s>

        {{-- Blended Background Image --}}
        <div class="absolute inset-0 z-0 select-none pointer-events-none">
            @if($event->image_path)
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ $event->title }}" class="w-full h-full object-cover opacity-10">
            @else
                <img src="{{ asset('images/togethernet-feature.jpg') }}" alt="{{ $event->title }}" class="w-full h-full object-cover opacity-5">
            @endif
            <div class="absolute inset-0 bg-linear-to-b from-zinc-950/20 via-zinc-950/60 to-zinc-950"></div>
            <div class="absolute inset-0 bg-orange-400/[0.01] mix-blend-color"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center justify-center text-center max-w-4xl px-4 gap-4">
            <x-svg.app-logo.text.light class="h-12 w-auto mb-4 opacity-80" />

            <flux:heading level="1" class="text-4xl md:text-6xl font-black uppercase tracking-wider text-zinc-200">
                {{ $event->title }}
            </flux:heading>

            <flux:separator class="w-32 my-4" />

            <div class="flex flex-col items-center">
                <div class="text-[8rem] md:text-[12rem] font-black leading-none text-orange-400 drop-shadow-[0_0_55px_rgba(251,146,60,0.3)] select-none">
                    {{ $daysLeft }}
                </div>
                <div class="text-lg md:text-xl font-bold uppercase tracking-[0.3em] text-zinc-400 mt-2">
                    {{ __('Days Until Event') }}
                </div>
            </div>
        </div>

        <div class="absolute bottom-8 left-0 right-0 z-10 flex items-center justify-center opacity-60">
            <x-signature :with-legal="false" />
        </div>
    </div>
</flux:main>
