<flux:main class="flex min-h-screen flex-col transition-opacity opacity-100 duration-650 lg:grow starting:opacity-0">
    <div class="fixed inset-0 bg-zinc-950 text-zinc-100 overflow-hidden flex flex-col p-6 gap-6">
        {{-- Header --}}
        <div class="h-20 shrink-0 bg-zinc-900 border border-zinc-800 rounded-3xl shadow-sm px-8 flex items-center justify-between relative overflow-hidden">
            <div class="flex items-center gap-3">
                <flux:badge color="orange" size="lg" class="px-4 py-1 flex flex-col items-center justify-center min-h-14">
                    <span class="text-xl font-black leading-none text-orange-400">{{ $activeCount }}</span>
                    <flux:text size="xs" class="font-bold uppercase tracking-widest opacity-70 text-orange-400/70 leading-none mt-1">{{ __('Active') }}</flux:text>
                </flux:badge>
                <flux:badge color="zinc" size="lg" class="px-4 py-1 flex flex-col items-center justify-center min-h-14">
                    <span class="text-xl font-black leading-none">{{ $totalCount }}</span>
                    <flux:text size="xs" class="font-bold uppercase tracking-widest opacity-70 leading-none mt-1">{{ __('Total') }}</flux:text>
                </flux:badge>
            </div>

            <div class="flex items-center gap-4">
                <x-svg.app-logo.text.light class="h-10 w-auto block leading-none" />
                <flux:separator vertical />
                <flux:heading size="xl" class="uppercase tracking-tight truncate max-w-xl leading-none">
                    {{ $event->title }}
                </flux:heading>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 min-h-0 grid grid-cols-12 gap-6 overflow-hidden">
            {{-- Leaderboard Table --}}
            <flux:card class="col-span-8 flex flex-col min-h-0 rounded-[2.5rem] p-8 shadow-sm border-zinc-800">
                <div class="flex items-center gap-3 mb-8 shrink-0">
                    <div class="p-2 bg-orange-400 rounded-xl">
                        <flux:icon.sparkles class="size-6 text-white" />
                    </div>
                    <flux:heading level="2" size="xl" class="uppercase tracking-tight">{{ __('Top Hunters') }}</flux:heading>
                </div>

                <div class="flex-1 min-h-0 overflow-hidden">
                    <flux:table class="h-full">
                        <flux:table.columns>
                            <flux:table.column class="w-20 text-center uppercase tracking-widest text-xs font-black" align="center">{{ __('Rank') }}</flux:table.column>
                            <flux:table.column class="pl-4 uppercase tracking-widest text-xs font-black">{{ __('Participant') }}</flux:table.column>
                            <flux:table.column class="text-center w-32 uppercase tracking-widest text-xs font-black" align="center">{{ __('Tags') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse($leaderboard as $index => $participant)
                                <flux:table.row class="h-[18%] border-b border-zinc-800/50 last:border-0">
                                    <flux:table.cell class="text-center">
                                        <span class="text-3xl font-black {{ $index === 0 ? 'text-orange-400' : 'text-zinc-300' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell class="pl-4">
                                        <div class="flex items-center gap-5">
                                            <div class="relative">
                                                <flux:avatar src="{{ $participant->user->profile_picture }}" :initials="$participant->user->initials()" class="size-16 ring-4 bg-zinc-400 {{ $index === 0 ? 'ring-orange-400/20' : 'ring-zinc-800' }}" />
                                                @if($index === 0)
                                                    <div class="absolute -top-2 -right-2 bg-orange-400 text-white p-1 rounded-full shadow-lg">
                                                        <flux:icon.sparkles class="size-4" />
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-2xl font-black leading-tight">{{ $participant->user->name }}</div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-center">
                                        <div class="inline-flex flex-col items-center justify-center bg-zinc-800/50 px-6 py-2 rounded-2xl border border-zinc-700 min-w-[80px]">
                                            <span class="text-3xl font-black text-orange-400 leading-none">{{ $participant->qr_tag_count }}</span>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="h-full text-center">
                                        <flux:text size="xl" class="italic text-zinc-400">{{ __('No tags yet. Let the hunt begin!') }}</flux:text>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>

            {{-- Sidebar --}}
            <div class="col-span-4 flex flex-col gap-6 min-h-0">
                <flux:card class="flex-1 min-h-0 rounded-[2.5rem] overflow-hidden flex flex-col shadow-sm p-0 border-zinc-800 relative bg-zinc-900">
                    {{-- Blended Background Image --}}
                    <div class="absolute inset-0 z-0">
                        @if($event->image_path)
                            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ $event->title }}" class="w-full h-full object-cover opacity-40">
                        @else
                            <img src="{{ asset('images/togethernet-feature.jpg') }}" alt="{{ $event->title }}" class="w-full h-full object-cover opacity-20">
                        @endif

                        {{-- Aggressive blending gradients --}}
                        <div class="absolute inset-0 bg-linear-to-b from-zinc-900/10 via-zinc-900/60 to-zinc-900"></div>
                        <div class="absolute inset-0 bg-linear-to-t from-zinc-900/20 to-transparent"></div>

                        {{-- Subtle orange atmosphere --}}
                        <div class="absolute inset-0 bg-orange-400/5 mix-blend-color"></div>
                    </div>

                    <div class="relative z-10 flex-1 p-10 flex flex-col justify-end gap-10 min-h-0 overflow-hidden">
                        <h1 class="text-3xl font-black uppercase tracking-[0.3em] text-zinc-400 mb-2">{{ __('Rules') }}</h1>

                        <div class="space-y-6">
                            <div class="flex items-center gap-5">
                                <div class="size-8 rounded-xl bg-orange-400 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-[0_0_15px_rgba(251,146,60,0.3)]">1</div>
                                <flux:text size="lg" class="font-bold leading-snug">{{ __('Find target on your event page.') }}</flux:text>
                            </div>
                            <div class="flex items-center gap-5">
                                <div class="size-8 rounded-xl bg-orange-400 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-[0_0_15px_rgba(251,146,60,0.3)]">2</div>
                                <flux:text size="lg" class="font-bold leading-snug">{{ __('Scan their QR code to tag.') }}</flux:text>
                            </div>
                            <div class="flex items-center gap-5">
                                <div class="size-8 rounded-xl bg-orange-400 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-[0_0_15px_rgba(251,146,60,0.3)]">3</div>
                                <flux:text size="lg" class="font-bold leading-snug">{{ __('Inherit target and climb!') }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:card>

                <flux:card class="h-24 shrink-0 bg-orange-400 border-zinc-800 rounded-4xl flex items-center justify-center shadow-lg px-8 gap-4 overflow-hidden">
                    <h1 class="text-white font-bold text-3xl uppercase tracking-[0.25em] animate-pulse">{{ __('Game Active') }}</h1>
                </flux:card>
            </div>
        </div>

        <div class="h-6 shrink-0 flex items-center justify-center opacity-80">
            <x-signature /> {{-- Togethernet and Fedor Romanov's (original developer) copyrights --}}
        </div>
    </div>
</flux:main>
