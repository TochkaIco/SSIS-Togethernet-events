<div class="p-6">
    <flux:heading size="xl" level="1">{{ __('My Feedback') }}</flux:heading>
    <flux:subheading>{{ __('Review bug reports and feature requests you\'ve submitted.') }}</flux:subheading>

    <flux:separator class="my-6" />

    <div class="flex flex-col items-start md:flex-row md:items-center gap-4 mb-4 w-full">
        <flux:input
            wire:model.live="search"
            icon="magnifying-glass"
            placeholder="{{ __('Search by comment...') }}"
            class="flex-1"
        />

        <flux:switch wire:model.live="filterResolved" label="{{ __('Only unresolved') }}" />
    </div>

    {{-- Users Table for Desktop --}}
    <flux:table class="hidden md:table">
        <flux:table.columns>
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            <flux:table.column>{{ __('Comment') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Date') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($feedbacks as $feedback)
                <flux:table.row :key="$feedback->id">
                    <flux:table.cell>
                        @if($feedback->type === \App\FeedbackType::BUG)
                            <flux:badge color="red" icon="bug-ant">{{ __('Bug') }}</flux:badge>
                        @elseif ($feedback->type === \App\FeedbackType::FEATURE)
                            <flux:badge color="purple" icon="sparkles">{{ __('Feature') }}</flux:badge>
                        @elseif($feedback->type === \App\FeedbackType::QOL)
                            <flux:badge color="blue" icon="viewfinder-circle">QOL</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="max-w-40 truncate">
                        <flux:button-or-div wire:click="openFeedbackModal({{ $feedback }})" class="hover:text-orange-300 hover:underline cursor-pointer">
                            {{ $feedback->comment }}
                        </flux:button-or-div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($feedback->is_rejected)
                            <flux:badge color="red" variant="subtle">{{ __('Rejected') }}</flux:badge>
                        @elseif($feedback->is_finished)
                            <flux:badge color="blue">{{ __('Resolved') }}</flux:badge>
                        @else
                            <flux:badge color="orange">{{ __('Unresolved') }}</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell align="end" class="whitespace-nowrap">
                        {{ $feedback->created_at->format('M j, Y') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Users Table for Mobile --}}
    <div class="md:hidden space-y-4">
        @foreach ($feedbacks as $feedback)
            <div class="p-4 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl space-y-4">
                <div class="flex items-center justify-between space-x-2">
                    <div>
                        <div class="flex items-center gap-3 text-left">
                            <flux:avatar circle class="size-12" :initials="$feedback->user->initials()" :src="$feedback->user->profile_picture" />
                            <div class="font-semibold text-zinc-800 dark:text-white">{{ $feedback->user->name }}</div>
                        </div>
                    </div>

                    <div>
                        @if($feedback->type === \App\FeedbackType::BUG)
                            <flux:badge color="red" icon="bug-ant">{{ __('Bug') }}</flux:badge>
                        @elseif ($feedback->type === \App\FeedbackType::FEATURE)
                            <flux:badge color="purple" icon="sparkles">{{ __('Feature') }}</flux:badge>
                        @elseif($feedback->type === \App\FeedbackType::QOL)
                            <flux:badge color="blue" icon="viewfinder-circle">QOL</flux:badge>
                        @endif
                    </div>
                </div>

                <flux:separator />

                <div class="flex flex-col gap-4 text-sm">
                    <div class="space-y-3">
                        <div class="flex flex-col">
                            <a class="cursor-pointer" icon="eye" wire:click="openFeedbackModal({{ $feedback }})">
                                {{ $feedback->comment }}
                            </a>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                @if($feedback->is_rejected)
                                    <flux:badge color="red" variant="subtle">{{ __('Rejected') }}</flux:badge>
                                @elseif($feedback->is_finished)
                                    <flux:badge color="blue">{{ __('Resolved') }}</flux:badge>
                                @else
                                    <flux:badge color="orange">{{ __('Unresolved') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <flux:pagination :paginator="$feedbacks" scroll-to class="mt-3" />

    <flux:modal name="feedback-modal-view" class="md:w-md space-y-6" background-blur>
        <div>
            <flux:heading size="lg">{{ __('Feedback') }}</flux:heading>
            <flux:subheading>
                <div class="flex items-center gap-2 mt-2">
                    <flux:avatar :src="$feedback->user->profile_picture" :initials="$feedback->user->initials()" size="xs" />
                    {{ $feedback->user->name }}
                </div>
            </flux:subheading>
        </div>

        <flux:radio.group wire:model="feedback_type" variant="segmented" class="w-full">
            @foreach(\App\FeedbackType::cases() as $case)
                <flux:radio :value="$case->value" :label="$case->label()" disabled />
            @endforeach
        </flux:radio.group>

        <flux:field>
            <flux:textarea
                wire:model="feedback_comment"
                rows="4"
                variant="filled"
                disabled
            />
        </flux:field>

        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="primary">{{ __('Close') }}</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>
