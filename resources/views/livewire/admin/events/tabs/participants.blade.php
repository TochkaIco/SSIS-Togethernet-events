<div>
    @haspermission('manage users')
    {{-- Header & Search --}}
    <div class="flex flex-col lg:flex-row gap-4 m-1 mb-6 lg:items-end">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Search participants...') }}"
            class="flex-1"
        />

        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            {{-- The Worker Filter Toggle --}}
            <flux:checkbox
                wire:model.live="onlyWorkers"
                label="{{ __('Workers Only') }}"
                class="sm:pb-2"
            />

            <div class="flex gap-2 flex-1">
                <flux:select wire:model.live="filterPaid" placeholder="{{ __('Payment Status') }}" class="flex-1 lg:w-48">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    <flux:select.option value="1">{{ __('Paid') }}</flux:select.option>
                    <flux:select.option value="0">{{ __('Unpaid') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="filterClassGroup" placeholder="{{ __('Filter by Class') }}" class="flex-1 lg:w-fit">
                    <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                    @foreach($allClassGroups as $classGroup)
                        <flux:select.option :value="$classGroup">{{ ucfirst($classGroup) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </div>

    {{-- Participants Table --}}
    <flux:table class="hidden md:table">
        <flux:table.columns>
            <flux:table.column>{{ __('Index') }}</flux:table.column>
            <flux:table.column>{{ __('Participant') }}</flux:table.column>
            <flux:table.column>{{ __('Class') }}</flux:table.column>
            @if($event->one_hour_periods)
                <flux:table.column>{{ __('Period') }}</flux:table.column>
            @endif
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            @if($event->paid_entry === 1)
                <flux:table.column>{{ __('Paid') }}</flux:table.column>
            @endif
            <flux:table.column>{{ __('Arrived') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($participants as $participant)
                <flux:table.row :key="'part-'.$participant->id" class="hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors group">
                    {{-- Pagination-aware Index --}}
                    <flux:table.cell class="text-center">
                        {{ $participants->firstItem() + $loop->index }}
                    </flux:table.cell>

                    {{-- Avatar & Name --}}
                    <flux:table.cell>
                        <div wire:click="viewUserProfile({{ $participant->id }})" class="flex items-center gap-x-3 cursor-pointer hover:text-orange-300 w-min">
                            <div class="relative">
                                <flux:avatar class="size-10" circle :initials="$participant->user->initials()" :src="$participant->user->profile_picture" />
                                @if($participant->is_disabled)
                                    <div class="absolute -top-1 -right-1 size-4 bg-red-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                        <flux:icon.x-mark class="size-2.5 text-white" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span @class(['font-medium', 'text-zinc-400 line-through' => $participant->is_disabled])>{{ $participant->user->name }}</span>
                                    @if($participant->is_disabled)
                                        <flux:badge size="xs" color="red" inset="top bottom">{{ __('Disabled') }}</flux:badge>
                                    @endif
                                </div>
                                <span class="text-xs mt-1">{{ $participant->user->email }}</span>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <x-class-badge :user-class="$participant->user->class ?? 'Unknown'" />
                    </flux:table.cell>

                    {{-- Period Selection --}}
                    @if($event->one_hour_periods)
                        <flux:table.cell>
                            <flux:select
                                wire:model.live="participantPeriods.{{ $participant->id }}"
                                wire:change="changePeriod({{ $participant->id }})"
                                size="sm"
                                class="w-32"
                            >
                                @foreach($event->eventPeriods() as $item)
                                    @if($item->type === 'period')
                                        <flux:select.option
                                            value="{{ $item->id }}"
                                        >
                                            {{ $item->label }}
                                        </flux:select.option>
                                    @endif
                                @endforeach
                            </flux:select>
                        </flux:table.cell>
                    @endif

                    {{-- Highlight Workers --}}
                    <flux:table.cell>
                        @if($this->participantIsWorking($participant->id))
                            <div class="flex items-center space-x-1">
                                <flux:button wire:click="updateParticipantWorkingStatus({{ $participant->id }})" variant="ghost" icon="chevron-double-down" size="xs" />
                                <flux:badge size="sm" color="orange">{{ __('Worker') }}</flux:badge>
                            </div>
                        @else
                            <div class="flex items-center space-x-1">
                                <flux:button wire:click="updateParticipantWorkingStatus({{ $participant->id }})" variant="ghost" icon="chevron-double-up" size="xs" />
                                <span class="text-xs">{{ __('Attendee') }}</span>
                            </div>
                        @endif
                    </flux:table.cell>

                    {{-- Payment Toggle --}}
                    @if($event->paid_entry === 1)
                        <flux:table.cell>
                            <flux:checkbox
                                wire:change="togglePaid({{ $participant->id }})"
                                :checked="(bool) $participant->has_paid"
                            />
                        </flux:table.cell>
                    @endif

                    {{-- Arrival Toggle --}}
                    <flux:table.cell>
                        <flux:checkbox
                            wire:change="toggleArrived({{ $participant->id }})"
                            :checked="(bool) $participant->has_arrived"
                        />
                    </flux:table.cell>

                    {{-- Actions Dropdown --}}
                    <flux:table.cell align="end">
                        <flux:dropdown class="mr-2">
                            <flux:button variant="subtle" icon="ellipsis-horizontal" size="sm" class="cursor-pointer" />
                            <flux:menu>
                                @if($event->event_type !== \App\EventType::QR_TAG)
                                    <flux:menu.item
                                        wire:click="moveToWaitingList({{ $participant->id }})"
                                        icon="list-bullet"
                                        class="cursor-pointer"
                                    >
                                        {{ __('Move to Waiting List') }}
                                    </flux:menu.item>

                                @else
                                    <flux:menu.item
                                        wire:click="toggleDisabled({{ $participant->id }})"
                                        :icon="$participant->is_disabled ? 'user-plus' : 'user-minus'"
                                        class="cursor-pointer"
                                    >
                                        {{ $participant->is_disabled ? __('Enable Player') : __('Disable Player') }}
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-12">
                        <div class="flex flex-col items-center justify-center">
                            <flux:icon.users class="size-12 mb-4" />
                            <flux:heading>{{ __('No participants found') }}</flux:heading>
                            <flux:subheading>{{ __('Try adjusting your search or filters.') }}</flux:subheading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Participants for Mobile --}}
    <div class="md:hidden space-y-4">
        @forelse ($participants as $participant)
            <div class="p-4 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl space-y-4">
                {{-- Header: Avatar, Name, and Actions --}}
                <div class="flex items-start justify-between gap-2">
                    <button wire:click="viewUserProfile({{ $participant->id }})" class="flex items-center gap-3 text-left min-w-0">
                        <div class="relative">
                            <flux:avatar circle class="size-12 shrink-0" :initials="$participant->user->initials()" :src="$participant->user->profile_picture" />
                            @if($participant->is_disabled)
                                <div class="absolute -top-1 -right-1 size-5 bg-red-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                    <flux:icon.x-mark class="size-3 text-white" />
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @if($participant->is_disabled)
                                    <div class="font-semibold text-zinc-400 truncate line-through">{{ Str::limit($participant->user->name, 15) }}</div>
                                    <flux:badge size="xs" color="red" inset="top bottom">{{ __('Disabled') }}</flux:badge>
                                @else
                                    <div class="font-semibold text-zinc-800 dark:text-white truncate">{{ Str::limit($participant->user->name, 20) }}</div>
                                @endif
                            </div>
                            <div class="text-xs text-zinc-500 truncate">{{ $participant->user->email }}</div>
                            <div class="text-sm text-zinc-500">#{{ $participants->firstItem() + $loop->index }}</div>
                        </div>
                    </button>

                    <div class="shrink-0">
                        <flux:dropdown>
                            <flux:button variant="subtle" icon="ellipsis-horizontal" size="sm" class="cursor-pointer" />
                            <flux:menu>
                                @if($event->event_type !== \App\EventType::QR_TAG)
                                    <flux:menu.item
                                        wire:click="moveToWaitingList({{ $participant->id }})"
                                        icon="list-bullet"
                                        class="cursor-pointer"
                                    >
                                        {{ __('Move to Waiting List') }}
                                    </flux:menu.item>
                                @else
                                    <flux:menu.item
                                        wire:click="toggleDisabled({{ $participant->id }})"
                                        :icon="$participant->is_disabled ? 'user-plus' : 'user-minus'"
                                        class="cursor-pointer"
                                    >
                                        {{ $participant->is_disabled ? __('Enable Player') : __('Disable Player') }}
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>

                <flux:separator />

                {{-- Body: Details Grid --}}
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Class') }}</flux:label>
                        <div>
                            <x-class-badge :user-class="$participant->user->class ?? 'Unknown'" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Type') }}</flux:label>
                        <div>
                            @if($this->participantIsWorking($participant->id))
                                <div class="flex items-center space-x-1">
                                    <flux:button wire:click="updateParticipantWorkingStatus({{ $participant->id }})" variant="ghost" icon="chevron-double-down" size="xs" />
                                    <flux:badge size="sm" color="orange">{{ __('Worker') }}</flux:badge>
                                </div>
                            @else
                                <div class="flex items-center space-x-1">
                                    <flux:button wire:click="updateParticipantWorkingStatus({{ $participant->id }})" variant="ghost" icon="chevron-double-up" size="xs" />
                                    <span class="text-xs">{{ __('Attendee') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($event->one_hour_periods)
                        <div class="col-span-2 space-y-1">
                            <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Period') }}</flux:label>
                            <flux:select
                                wire:model.live="participantPeriods.{{ $participant->id }}"
                                wire:change="changePeriod({{ $participant->id }})"
                                size="sm"
                                class="w-full"
                            >
                                @foreach($event->eventPeriods() as $item)
                                    @if($item->type === 'period')
                                        <flux:select.option value="{{ $item->id }}">
                                            {{ $item->label }}
                                        </flux:select.option>
                                    @endif
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    <div class="flex items-center justify-between col-span-2">
                        @if($event->paid_entry === 1)
                            <div class="flex items-center gap-2">
                                <flux:checkbox
                                    wire:change="togglePaid({{ $participant->id }})"
                                    :checked="(bool) $participant->has_paid"
                                    :label="__('Paid')"
                                />
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            <flux:checkbox
                                wire:change="toggleArrived({{ $participant->id }})"
                                :checked="(bool) $participant->has_arrived"
                                :label="__('Arrived')"
                            />
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl">
                <flux:icon.users class="size-10 mx-auto mb-3 opacity-50" />
                <flux:heading>{{ __('No participants found') }}</flux:heading>
            </div>
        @endforelse
    </div>

    <flux:pagination :paginator="$participants" scroll-to class="mt-3" />
    @endhaspermission
</div>
