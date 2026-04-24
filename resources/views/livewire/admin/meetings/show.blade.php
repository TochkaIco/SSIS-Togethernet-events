<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <flux:button icon="chevron-left" variant="ghost" :href="route('admin.meetings.index')" wire:navigate size="sm" class="-ml-2 mb-2">
                {{ __('Back to Meetings') }}
            </flux:button>
            <flux:heading size="xl" class="mb-1">{{ $meeting->title }}</flux:heading>
            <flux:subheading class="flex items-center gap-2">
                <flux:icon.calendar variant="mini" class="size-4" />
                {{ $meeting->meeting_starts_at->format('Y-m-d H:i') }}
                @if($meeting->meeting_ends_at)
                    - {{ $meeting->meeting_ends_at->format('H:i') }}
                @endif
            </flux:subheading>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-2">
            @if(!$meeting->meeting_ends_at)
                <flux:button wire:click="endMeeting" variant="subtle" icon="clock">
                    {{ __('End Meeting') }}
                </flux:button>
            @endif
            <flux:button :href="route('admin.meetings.protocol', $meeting)" variant="primary" icon="pencil-square" wire:navigate>
                {{ __('Edit Protocol') }}
            </flux:button>
            @if(! \Carbon\Carbon::now()->greaterThan($meeting->meeting_starts_at->addMinutes(20)))
                    <flux:button
                        wire:click="confirmDelete"
                        variant="ghost"
                        icon="trash"
                        class="hover:text-red-600 cursor-pointer"
                    />
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
        <div class="xl:col-span-2 space-y-6">
            <flux:card>
                <div class="flex items-center justify-between mb-6 border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <flux:heading size="lg">{{ __('Protocol') }}</flux:heading>
                    <flux:icon.book-open variant="mini" class="size-5 text-zinc-400" />
                </div>

                <div class="prose dark:prose-invert max-w-none min-h-[400px]">
                    @if($meeting->description)
                        {!! $meeting->description !!}
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-zinc-400 italic">
                            <flux:icon.pencil-square class="size-12 mb-4 opacity-20" />
                            <p>{{ __('No protocol written yet.') }}</p>
                            <flux:button :href="route('admin.meetings.protocol', $meeting)" variant="ghost" size="sm" class="mt-4" wire:navigate>
                                {{ __('Start writing') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card>
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <flux:heading size="lg">{{ __('Attendance') }}</flux:heading>
                        <flux:badge size="sm" variant="subtle" color="orange">{{ $users->count() }} {{ __('Members') }}</flux:badge>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <flux:dropdown>
                            <flux:button icon-trailing="chevron-down" size="sm" variant="subtle">
                                {{ __('Classes') }} @if(count($selectedClasses) > 0) ({{ count($selectedClasses) }}) @endif
                            </flux:button>

                            <flux:menu class="min-w-48">
                                <flux:menu.checkbox.group wire:model.live="selectedClasses">
                                    @foreach($allClasses as $class)
                                        <flux:menu.checkbox :value="$class" :label="$class" />
                                    @endforeach
                                </flux:menu.checkbox.group>

                                @if(count($selectedClasses) > 0)
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="$set('selectedClasses', [])" variant="danger">
                                        {{ __('Clear filters') }}
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>

                        @if(count($selectedClasses) > 0)
                            <flux:badge size="sm" color="orange" closable wire:click="$set('selectedClasses', [])">
                                {{ count($selectedClasses) }} {{ __('classes') }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                <div class="space-y-1 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($users as $user)
                        <div class="group flex items-center justify-between py-2 px-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 rounded-lg transition-colors">
                            <div class="flex items-center gap-3">
                                <flux:avatar :src="$user->profile_picture" :name="$user->name" size="sm" class="shrink-0" />
                                <div class="text-sm">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100 leading-none mb-1">{{ $user->name }}</p>
                                    <p class="text-[10px] text-zinc-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div
                                @class(['pointer-events-none opacity-50 cursor-not-allowed' => !auth()->user()->hasPermissionTo('take attendance')])
                                title="You don't have permission to take attendance"
                            >
                                <flux:switch
                                    wire:click="toggleAttendance('{{ $user->id }}')"
                                    :checked="in_array($user->id, $attendedUserIds)"
                                    size="sm"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-dashed">
                <div class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                    <flux:icon.information-circle variant="mini" class="size-5 shrink-0" />
                    <p>{{ __('Only users with the "tog-member" role are shown in the attendance list.') }}</p>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- Modal: Dangerous Delete --}}
    <flux:modal name="confirm-meeting-deletion" class="md:w-100">
        <div x-data="{ canDelete: false, timer: 3 }"
             x-on:modal-show.window="canDelete = false; timer = 3; let interval = setInterval(() => { if(timer > 0) { timer-- } else { canDelete = true; clearInterval(interval) } }, 1000)"
        >
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Confirm Deletion') }}</flux:heading>
                    <flux:subheading>
                        {{ __('This action cannot be undone. You must wait ') }}<span x-text="timer" class="font-bold text-red-600"></span>{{ __('s to confirm.') }}
                    </flux:subheading>
                </div>

                <div class="flex gap-2 justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button
                        wire:click="deleteMeeting"
                        variant="danger"
                        class="cursor-pointer"
                        x-bind:disabled="!canDelete"
                        x-bind:class="!canDelete && 'opacity-50 grayscale'"
                    >
                        <span x-show="canDelete">{{ __('Delete Permanently') }}</span>
                        <span x-show="!canDelete">{{ __('Wait') }} (<span x-text="timer"></span>s)</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
