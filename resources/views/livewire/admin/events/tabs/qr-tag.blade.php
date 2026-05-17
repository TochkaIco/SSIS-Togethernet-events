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

                <flux:modal.trigger name="confirm-respawn-all">
                    <flux:button variant="primary" icon="sparkles" class="bg-purple-600 hover:bg-purple-700 text-white flex-1 sm:flex-none">
                        {{ __('Respawn All') }}
                    </flux:button>
                </flux:modal.trigger>
            @else
                <flux:button wire:click="startShuffle" variant="primary" icon="arrow-path" class="flex-1 sm:flex-none">
                    {{ __('Shuffle & Start') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by name or email...') }}" class="flex-1" />

        <div class="flex gap-2 md:gap-4">
            <flux:select wire:model.live="filterClassGroup" placeholder="{{ __('Filter by Class') }}" class="md:w-64 flex-1">
                <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                @foreach($allClassGroups as $classGroup)
                    <flux:select.option :value="$classGroup">{{ ucfirst($classGroup) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterRole" placeholder="{{ __('Filter by Role') }}" class="md:w-64 flex-1">
                <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                @foreach($allRoles as $role)
                    <flux:select.option :value="$role->name">{{ ucfirst($role->name) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Desktop Table --}}
    <flux:table class="hidden lg:table">
        <flux:table.columns>
            <flux:table.column>{{ __('Participant') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Tags') }}</flux:table.column>
            <flux:table.column>{{ __('Target') }}</flux:table.column>
            <flux:table.column>{{ __('Tagged By') }}</flux:table.column>
            <flux:table.column>{{ __('Tagged At') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($participants as $participant)
                <flux:table.row>
                    <flux:table.cell class="flex items-center gap-2">
                        <div class="relative">
                            <flux:avatar src="{{ $participant->user->profile_picture }}" :initials="$participant->user->initials()" size="sm" />
                            @if($participant->is_disabled)
                                <div class="absolute -top-1 -right-1 size-4 bg-red-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                    <flux:icon.x-mark class="size-2.5 text-white" />
                                </div>
                            @endif
                        </div>
                        <div>
                            <div @class(['font-medium', 'text-zinc-400 line-through' => $participant->is_disabled])>{{ $participant->user->name }}</div>
                            <div class="text-xs text-muted-foreground">{{ $participant->user->email }}</div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($participant->is_disabled)
                            <flux:badge color="red" size="sm">{{ __('Disabled') }}</flux:badge>
                        @elseif ($participant->qr_tag_tagged_at)
                            <flux:badge color="red" size="sm">{{ __('Tagged') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge variant="outline" size="sm">{{ $participant->qr_tag_count }}</flux:badge>
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
                        <div class="flex items-center gap-2">
                            @if ($participant->qr_tag_tagged_at && !$participant->is_disabled)
                                <flux:button wire:click="respawnPlayer({{ $participant->id }})" variant="ghost" icon="sparkles" size="sm" class="text-purple-600 hover:text-purple-700" tooltip="{{ __('Respawn Player') }}" />
                            @endif

                            @if($participant->is_disabled)
                                <flux:button
                                    wire:click="toggleDisabled({{ $participant->id }})"
                                    variant="ghost"
                                    icon="user-plus"
                                    size="sm"
                                    class="text-green-600"
                                    :tooltip="__('Enable Player')"
                                />
                            @else
                                <flux:modal.trigger name="confirm-disable-player">
                                    <flux:button
                                        wire:click="$set('selectedRegistrationId', {{ $participant->id }})"
                                        variant="ghost"
                                        icon="user-minus"
                                        size="sm"
                                        class="text-zinc-400"
                                        :tooltip="__('Disable Player')"
                                    />
                                </flux:modal.trigger>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$participants" scroll-to class="hidden lg:block mt-3" />

    {{-- Mobile/Tablet List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:hidden">
        @foreach ($participants as $participant)
            <flux:card class="relative overflow-hidden">
                <div class="flex items-center gap-3 mb-4">
                    <div class="relative">
                        <flux:avatar src="{{ $participant->user->profile_picture }}" :initials="$participant->user->initials()" size="md" />
                        @if($participant->is_disabled)
                            <div class="absolute -top-1 -right-1 size-5 bg-red-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                <flux:icon.x-mark class="size-3 text-white" />
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div @class(['font-bold truncate', 'text-zinc-400 line-through' => $participant->is_disabled])>{{ $participant->user->name }}</div>
                        <div class="text-xs text-muted-foreground truncate">{{ $participant->user->email }}</div>
                    </div>
                    <div class="ml-auto">
                        @if ($participant->is_disabled)
                            <flux:badge color="red" size="sm">{{ __('Disabled') }}</flux:badge>
                        @elseif ($participant->qr_tag_tagged_at)
                            <flux:badge color="red" size="sm">{{ __('Tagged') }}</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">{{ __('Active') }}</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-muted-foreground">{{ __('Total Tags:') }}</span>
                        <flux:badge variant="outline" size="sm">{{ $participant->qr_tag_count }}</flux:badge>
                    </div>

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

                    @if($participant->qr_tag_tagged_at && !$participant->is_disabled)
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
                            <flux:button wire:click="respawnPlayer({{ $participant->id }})" variant="primary" icon="sparkles" size="sm" class="w-full bg-purple-600 hover:bg-purple-700 text-white">
                                {{ __('Respawn Player') }}
                            </flux:button>
                        </div>
                    @endif

                    <div class="mt-2">
                        @if($participant->is_disabled)
                            <flux:button
                                wire:click="toggleDisabled({{ $participant->id }})"
                                variant="outline"
                                icon="user-plus"
                                size="sm"
                                class="w-full"
                            >
                                {{ __('Enable Player') }}
                            </flux:button>
                        @else
                            <flux:modal.trigger name="confirm-disable-player">
                                <flux:button
                                    wire:click="$set('selectedRegistrationId', {{ $participant->id }})"
                                    variant="outline"
                                    icon="user-minus"
                                    size="sm"
                                    class="w-full"
                                >
                                    {{ __('Disable Player') }}
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    </div>
                </div>
            </flux:card>
        @endforeach

        <flux:pagination :paginator="$participants" scroll-to class="mt-3" />
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
                                @case('respawn')
                                @case('respawn_all')
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
                                @elseif($log->type === 'respawn')
                                    <span class="font-bold">{{ $log->user->name }}</span> {{ __('was respawned by admin') }} <span class="font-bold text-purple-600">{{ $log->admin?->name ?? __('Unknown Admin') }}</span>.
                                @elseif($log->type === 'respawn_all')
                                    {{ __('All players were respawned and targets reshuffled by admin') }} <span class="font-bold text-purple-600">{{ $log->admin?->name ?? __('Unknown Admin') }}</span>.
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

    <flux:modal name="confirm-disable-player" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Disable Player?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Are you sure you want to disable this player? They will be removed from the game cycle and their hunter will be assigned to their target.') }}
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="toggleDisabled" variant="danger" x-on:click="$flux.modal('confirm-disable-player').close()">
                    {{ __('Confirm Disable') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    @if ($event->isQrTagGameStarted())
        <flux:modal name="confirm-respawn-all" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Respawn All Players?') }}</flux:heading>
                    <flux:subheading>
                        {{ __('This will clear all "Tagged" statuses and reshuffle all participants into a new game.') }}
                    </flux:subheading>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button wire:click="respawnAll" variant="primary" x-on:click="$flux.modal('confirm-respawn-all').close()" class="bg-purple-600 hover:bg-purple-700 text-white">
                        {{ __('Confirm Respawn All') }}
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
</div>
