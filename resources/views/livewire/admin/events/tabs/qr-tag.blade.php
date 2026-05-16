<div>
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-medium">{{ __('QR-Tag Control') }}</h3>
            <p class="text-sm text-muted-foreground">{{ __('Manage players for QR-Tag.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            @if ($event->isQrTagGameStarted())
                <flux:modal.trigger name="confirm-reset-game">
                    <flux:button variant="outline" icon="trash" class="flex-1 sm:flex-none">
                        {{ __('Reset Game') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="confirm-rebirth-all">
                    <flux:button variant="primary" icon="sparkles" class="bg-purple-600 hover:bg-purple-700 text-white flex-1 sm:flex-none">
                        {{ __('Rebirth All') }}
                    </flux:button>
                </flux:modal.trigger>
            @else
                <flux:button wire:click="startShuffle" variant="primary" icon="arrow-path" class="flex-1 sm:flex-none">
                    {{ __('Shuffle & Start') }}
                </flux:button>
            @endif
        </div>
    </div>

    @if ($event->isQrTagGameStarted())
        <flux:modal name="confirm-rebirth-all" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Rebirth All Players?') }}</flux:heading>
                    <flux:subheading>
                        {{ __('This will clear all "Tagged" statuses and reshuffle all participants into a new game.') }}
                    </flux:subheading>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button wire:click="rebirthAll" variant="primary" x-on:click="$flux.modal('confirm-rebirth-all').close()" class="bg-purple-600 hover:bg-purple-700 text-white">
                        {{ __('Confirm Rebirth All') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="confirm-reset-game" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Reset QR Tag Game?') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Are you sure you want to reset the game? All progress and targets will be lost. This action cannot be undone.') }}
                    </flux:subheading>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="resetGame" variant="danger" x-on:click="$flux.modal('confirm-reset-game').close()">
                        {{ __('Reset Game') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Desktop Table --}}
    <flux:table class="hidden lg:table">
        <flux:table.columns>
            <flux:table.column>{{ __('Participant') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Target') }}</flux:table.column>
            <flux:table.column>{{ __('Tagged By') }}</flux:table.column>
            <flux:table.column>{{ __('Tagged At') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($participants as $participant)
                <flux:table.row>
                    <flux:table.cell class="flex items-center gap-2">
                        <flux:avatar src="{{ $participant->user->profile_picture }}" :initials="$participant->user->initials()" size="sm" />
                        <div>
                            <div class="font-medium">{{ $participant->user->name }}</div>
                            <div class="text-xs text-muted-foreground">{{ $participant->user->email }}</div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($participant->qr_tag_tagged_at)
                            <flux:badge color="red" size="sm">{{ __('Tagged') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($participant->targetUser)
                            <div class="flex items-center gap-2">
                                <flux:avatar src="{{ $participant->targetUser->profile_picture }}" :initials="$participant->targetUser->initials()" size="xs" />
                                <span>{{ $participant->targetUser->name }}</span>
                            </div>
                        @else
                            <span class="text-muted-foreground italic">{{ __('No target') }}</span>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($participant->taggedBy)
                            <div class="flex items-center gap-2">
                                <flux:avatar src="{{ $participant->taggedBy->profile_picture }}" :initials="$participant->taggedBy->initials()" size="xs" />
                                <span>{{ $participant->taggedBy->name }}</span>
                            </div>
                        @else
                            -
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $participant->qr_tag_tagged_at?->format('Y-m-d H:i') ?? '-' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($participant->qr_tag_tagged_at)
                            <flux:button wire:click="rebirthPlayer({{ $participant->id }})" variant="ghost" icon="sparkles" size="sm" class="text-purple-600 hover:text-purple-700" tooltip="{{ __('Rebirth Player') }}" />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Mobile/Tablet List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:hidden">
        @foreach ($participants as $participant)
            <flux:card class="relative overflow-hidden">
                <div class="flex items-center gap-3 mb-4">
                    <flux:avatar src="{{ $participant->user->profile_picture }}" :initials="$participant->user->initials()" size="md" />
                    <div class="min-w-0">
                        <div class="font-bold truncate">{{ $participant->user->name }}</div>
                        <div class="text-xs text-muted-foreground truncate">{{ $participant->user->email }}</div>
                    </div>
                    <div class="ml-auto">
                        @if ($participant->qr_tag_tagged_at)
                            <flux:badge color="red" size="sm">{{ __('Tagged') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-muted-foreground">{{ __('Target:') }}</span>
                        @if ($participant->targetUser)
                            <div class="flex items-center gap-2">
                                <flux:avatar src="{{ $participant->targetUser->profile_picture }}" :initials="$participant->targetUser->initials()" size="xs" />
                                <span class="font-medium">{{ $participant->targetUser->name }}</span>
                            </div>
                        @else
                            <span class="italic text-zinc-400">{{ __('No target') }}</span>
                        @endif
                    </div>

                    @if($participant->qr_tag_tagged_at)
                        <div class="flex justify-between items-center py-2 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="text-muted-foreground">{{ __('Tagged By:') }}</span>
                            @if ($participant->taggedBy)
                                <div class="flex items-center gap-2">
                                    <flux:avatar src="{{ $participant->taggedBy->profile_picture }}" :initials="$participant->taggedBy->initials()" size="xs" />
                                    <span class="font-medium">{{ $participant->taggedBy->name }}</span>
                                </div>
                            @else
                                <span>-</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-muted-foreground">{{ __('Tagged At:') }}</span>
                            <span class="font-medium">{{ $participant->qr_tag_tagged_at->format('Y-m-d H:i') }}</span>
                        </div>

                        <div class="mt-4">
                            <flux:button wire:click="rebirthPlayer({{ $participant->id }})" variant="primary" icon="sparkles" size="sm" class="w-full bg-purple-600 hover:bg-purple-700 text-white">
                                {{ __('Rebirth Player') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </flux:card>
        @endforeach
    </div>

    <div class="mt-12">
        <flux:heading size="lg" class="mb-4">{{ __('Game Log') }}</flux:heading>
        <flux:card class="p-0 sm:p-6">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($logs as $log)
                    <div class="flex items-start gap-3 text-sm p-4 sm:px-0 first:pt-0 last:pb-0">
                        <div class="mt-1 shrink-0">
                            @switch($log->type)
                                @case('tagged')
                                    <flux:icon.user-minus class="size-4 text-red-500" />
                                    @break
                                @case('rebirth')
                                @case('rebirth_all')
                                    <flux:icon.sparkles class="size-4 text-purple-500" />
                                    @break
                                @case('started')
                                    <flux:icon.play class="size-4 text-green-500" />
                                    @break
                                @case('reset')
                                    <flux:icon.arrow-path class="size-4 text-zinc-500" />
                                    @break
                            @endswitch
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="leading-relaxed">
                                @if($log->type === 'tagged')
                                    <span class="font-bold">{{ $log->user->name }}</span> {{ __('tagged') }} <span class="font-bold">{{ $log->targetUser->name }}</span>
                                @elseif($log->type === 'rebirth')
                                    <span class="font-bold">{{ $log->user->name }}</span> {{ __('was rebirthed by admin') }} <span class="font-bold text-purple-600">{{ $log->admin?->name ?? __('Unknown Admin') }}</span>.
                                @elseif($log->type === 'rebirth_all')
                                    {{ __('All players were rebirthed and targets reshuffled by admin') }} <span class="font-bold text-purple-600">{{ $log->admin?->name ?? __('Unknown Admin') }}</span>.
                                @elseif($log->type === 'started')
                                    {{ __('The game was started and targets were shuffled by admin') }} <span class="font-bold text-purple-600">{{ $log->admin?->name ?? __('Unknown Admin') }}</span>.
                                @elseif($log->type === 'reset')
                                    {{ __('The game was reset by admin') }} <span class="font-bold text-purple-600">{{ $log->admin?->name ?? __('Unknown Admin') }}</span>.
                                @endif
                            </div>
                            <div class="text-xs text-muted-foreground mt-1">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 sm:px-0">
                        <flux:text class="italic">{{ __('No logs yet.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:card>
    </div>
</div>
