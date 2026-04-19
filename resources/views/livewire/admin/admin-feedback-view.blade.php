<div class="p-6">
    <flux:heading size="xl" level="1">{{ __('User Feedback') }}</flux:heading>
    <flux:subheading>{{ __('Review bug reports and feature requests.') }}</flux:subheading>

    <flux:separator class="my-6" />

    <flux:table :paginate="$feedbacks">
        <flux:table.columns>
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

    <flux:modal name="feedback-modal-admin" class="md:w-md space-y-6" background-blur>
        <flux:heading size="lg">{{ __('Feedback Details') }}</flux:heading>

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
