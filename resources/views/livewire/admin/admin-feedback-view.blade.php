<div class="p-6">
    <flux:heading size="xl" level="1">{{ __('User Feedback') }}</flux:heading>
    <flux:subheading>{{ __('Review bug reports and feature requests.') }}</flux:subheading>

    <flux:separator class="my-6" />

    <div class="flex flex-col md:flex-row items-center gap-4 mb-4 w-full">
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
            <flux:table.column>{{ __('User') }}</flux:table.column>
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            <flux:table.column sortable>{{ __('Comment') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Date') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($feedbacks as $feedback)
                <flux:table.row :key="$feedback->id">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            @if($feedback->user)
                                <flux:avatar :src="$feedback->user->profile_picture" :initials="$feedback->user->initials()" size="xs" />
                                <a href="{{ route('admin.user.profile', $feedback->user) }}" class="text-sm hover:underline hover:text-orange-300">{{ $feedback->user->name }}</a>
                            @else
                                <flux:avatar size="xs" />
                                <span class="text-sm text-zinc-500 italic">{{ __('Guest') }}</span>
                            @endif
                        </div>
                    </flux:table.cell>

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
                        {{ $feedback->comment }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($feedback->is_finished === 1)
                            <flux:badge color="blue">{{ __('Resolved') }}</flux:badge>
                        @else
                            <flux:badge color="orange">{{ __('Unresolved') }}</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $feedback->created_at->format('M j, Y') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown class="flex justify-end mr-2">
                            <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />

                            <flux:menu>
                                <flux:menu.item class="cursor-pointer" icon="eye" wire:click="openUserFeedbackModal({{ $feedback }})">{{ __('View Details') }}</flux:menu.item>
                                @if($feedback->is_finished === 1)
                                    <flux:menu.item class="cursor-pointer" icon="x-mark" wire:click="markAsUnresolved({{ $feedback->id }})">{{ __('Unresolve') }}</flux:menu.item>
                                @else
                                    <flux:menu.item class="cursor-pointer" icon="check" wire:click="markAsResolved({{ $feedback->id }})">{{ __('Resolve') }}</flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Users Table for Mobile --}}
    <div class="md:hidden space-y-4">
        @foreach ($feedbacks as $feedback)
            <div class="p-4 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        @if($feedback->user)
                            <button href="{{ route('admin.user.profile', $feedback->user) }}" class="flex items-center gap-3 text-left">
                                <flux:avatar circle class="size-12" :initials="$feedback->user->initials()" :src="$feedback->user->profile_picture" />
                                <div class="font-semibold text-zinc-800 dark:text-white">{{ $feedback->user->name }}</div>
                            </button>
                        @else
                            <button class="flex items-center gap-3 text-left">
                                <flux:avatar circle class="size-12" initials="D" />
                                <div class="font-semibold text-zinc-800 dark:text-white">{{ __('Guest') }}</div>
                            </button>
                        @endif
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
                            <a class="cursor-pointer" icon="eye" wire:click="openUserFeedbackModal({{ $feedback }})">
                                {{ $feedback->comment }}
                            </a>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                @if($feedback->is_finished === 1)
                                    <flux:badge color="blue">{{ __('Resolved') }}</flux:badge>
                                @else
                                    <flux:badge color="orange">{{ __('Unresolved') }}</flux:badge>
                                @endif
                            </div>

                            <div>
                                @if($feedback->is_finished === 1)
                                    <flux:button class="cursor-pointer" variant="primary" size="xs" icon="x-mark" wire:click="markAsUnresolved({{ $feedback->id }})">{{ __('Unresolve') }}</flux:button>
                                @else
                                    <flux:button class="cursor-pointer" variant="primary" size="xs" icon="check" wire:click="markAsResolved({{ $feedback->id }})">{{ __('Resolve') }}</flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <flux:pagination :paginator="$feedbacks" scroll-to class="mt-3" />

    <flux:modal name="feedback-modal-admin" class="md:w-md space-y-6" background-blur>
        <div>
            <flux:heading size="lg">{{ __('Feedback Details') }}</flux:heading>
            @if($feedback_user = $this->selected_feedback?->user)
                <flux:subheading>
                    <div class="flex items-center gap-2 mt-2">
                        <flux:avatar :src="$feedback_user->profile_picture" :initials="$feedback_user->initials()" size="xs" />
                        {{ $feedback_user->name }}
                    </div>
                </flux:subheading>
            @else
                <flux:subheading>
                    <div class="flex items-center gap-2 mt-2 text-zinc-500 italic">
                        <flux:avatar size="xs" />
                        {{ __('Guest') }}
                    </div>
                </flux:subheading>
            @endif
        </div>

        <flux:radio.group wire:model="feedback_type" variant="segmented" class="w-full" disabled>
            @foreach(\App\FeedbackType::cases() as $case)
                <flux:radio :value="$case->value" :label="$case->label()" />
            @endforeach
        </flux:radio.group>

        <flux:field>
            <flux:textarea
                wire:model="feedback_comment"
                rows="4"
                variant="filled"
                readonly
            />
        </flux:field>
    </flux:modal>
</div>
