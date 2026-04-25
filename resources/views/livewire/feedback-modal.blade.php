<div>
    <flux:modal name="feedback-modal" class="md:w-md" background-blur>
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Feedback</flux:heading>

            <flux:radio.group wire:model="type" variant="segmented" class="w-full">
                <flux:radio icon="bug-ant" value="bug" :label="\App\FeedbackType::BUG->label()" class="cursor-pointer" />
                <flux:radio icon="sparkles" value="feature" :label="\App\FeedbackType::FEATURE->label()" class="cursor-pointer" />
                <flux:radio icon="viewfinder-circle" value="qol" :label="\App\FeedbackType::QOL->label()" class="cursor-pointer" />
            </flux:radio.group>

            <flux:field>
                <flux:textarea
                    wire:model="comment"
                    :placeholder="__('Describe the problem, suggested changes or functions you wish to see...')"
                    rows="4"
                    variant="filled"
                />
                <flux:error name="comment" />
            </flux:field>

            @auth
                <flux:checkbox wire:model="anonymous" :label="__('Send anonymously')" />
            @endauth

            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button class="cursor-pointer" type="submit" variant="primary">{{ __('Submit') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
