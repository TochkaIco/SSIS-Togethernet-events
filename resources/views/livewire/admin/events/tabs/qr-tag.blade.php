<div>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium">{{ __('QR Tag Control') }}</h3>
            <p class="text-sm text-muted-foreground">{{ __('Manage targets and progress for QR-Tag.') }}</p>
        </div>
        <div class="flex gap-2">
            <flux:modal.trigger name="confirm-reset-game">
                <flux:button variant="danger" icon="trash">
                    {{ __('Reset Game') }}
                </flux:button>
            </flux:modal.trigger>

            <flux:button wire:click="startShuffle" variant="primary" icon="arrow-path">
                {{ __('Shuffle & Start') }}
            </flux:button>
        </div>
    </div>

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

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Participant') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Target') }}</flux:table.column>
            <flux:table.column>{{ __('Tagged By') }}</flux:table.column>
            <flux:table.column>{{ __('Tagged At') }}</flux:table.column>
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
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
