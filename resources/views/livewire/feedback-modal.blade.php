<div>
    <flux:modal name="feedback-modal" class="md:w-md" background-blur>
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ __('Feedback') }}</flux:heading>

            <flux:radio.group wire:model="type" variant="segmented" class="w-full">
                <flux:radio icon="bug-ant" value="bug" :label="\App\FeedbackType::BUG->label()" class="cursor-pointer" />
                <flux:radio icon="sparkles" value="feature" :label="\App\FeedbackType::FEATURE->label()" class="cursor-pointer" />
                <flux:radio icon="viewfinder-circle" value="qol" :label="\App\FeedbackType::QOL->label()" class="cursor-pointer" />
            </flux:radio.group>

            <flux:field>
                <flux:textarea
                    wire:model="comment"
                    :placeholder="__('Describe the problem, suggested changes or functions you wish to see...')"
                    rows="auto"
                    autofocus
                    variant="filled"
                />
                <flux:error name="comment" />
            </flux:field>

            <flux:field>
                <div class="mt-1 space-y-3">
                    @if($image)
                        <div class="relative w-full max-h-40 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                            <img src="{{ $image->temporaryUrl() }}" class="w-full h-auto object-cover" alt="{{ __('Image') }}" />
                        </div>
                        <flux:button
                            type="button"
                            variant="danger"
                            size="sm"
                            wire:click.prevent="clearFeedbackImage"
                            data-test="remove-image-button"
                            class="w-full mt-2 cursor-pointer"
                        >
                            {{ __('Remove Image') }}
                        </flux:button>
                    @endif
                    <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 dark:file:bg-zinc-800 file:text-orange-700 dark:file:text-zinc-200 hover:file:bg-orange-100 dark:hover:file:bg-zinc-700 cursor-pointer" />
                </div>
                <flux:error name="image" />
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
