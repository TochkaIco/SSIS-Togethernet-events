<div class="p-6">
    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6 items-end">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="{{ __('Search participants...') }}"
            class="flex-1"
        />

        <div class="flex items-center gap-4">
            {{-- The Worker Filter Toggle --}}
            <flux:checkbox
                wire:model.live="onlyWorkers"
                label="{{ __('Workers Only') }}"
                class="pb-2"
            />

            <flux:select wire:model.live="filterPaid" placeholder="{{ __('Payment Status') }}" class="md:w-48">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="1">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="0">{{ __('Unpaid') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Participants Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Index') }}</flux:table.column>
            <flux:table.column>{{ __('Participant') }}</flux:table.column>
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
                            <flux:avatar class="size-10" circle :initials="$participant->initials()" :src="$participant->profile_picture" />
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $participant->name }}</span>
                                <span class="text-xs">{{ $participant->email }}</span>
                            </div>
                        </div>
                    </flux:table.cell>

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
                                :checked="(bool) $participant->pivot->has_paid"
                            />
                        </flux:table.cell>
                    @endif

                    {{-- Arrival Toggle --}}
                    <flux:table.cell>
                        <flux:checkbox
                            wire:change="toggleArrived({{ $participant->id }})"
                            :checked="(bool) $participant->pivot->has_arrived"
                        />
                    </flux:table.cell>

                    {{-- Actions Dropdown --}}
                    <flux:table.cell align="end">
                        <flux:dropdown class="mr-2">
                            <flux:button variant="subtle" icon="ellipsis-horizontal" size="sm" class="cursor-pointer" />
                            <flux:menu>
                                <flux:menu.item
                                    wire:click="moveToWaitingList({{ $participant->id }})"
                                    icon="list-bullet"
                                    class="cursor-pointer"
                                >
                                    {{ __('Move to Waiting List') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12">
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

    <div class="mt-6">
        {{ $participants->links() }}
    </div>
</div>
