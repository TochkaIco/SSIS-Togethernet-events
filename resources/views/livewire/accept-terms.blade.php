<div class="mx-auto max-w-4xl w-full py-6 lg:p-12">
    <div class="flex flex-col items-center mb-12">
        <x-svg.app-logo.text.light class="hidden h-10 md:h-22 w-auto dark:block" />
        <x-svg.app-logo.text.dark class="w-auto h-10 md:h-22 dark:hidden" />
    </div>

    <flux:card size="none" class="overflow-hidden space-y-0">
        <details class="group">
            <summary class="flex items-center justify-between p-6 pb-0 md:pb-6 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors list-none rounded-xl group-open:rounded-b-none [&::-webkit-details-marker]:hidden">
                <div class="pr-4">
                    <flux:heading size="xl" class="mb-1">{{ __('Terms of Service') }}</flux:heading>
                    <flux:text>{{ __('Please review and accept our updated terms to continue using TogethernetEvents.') }}</flux:text>
                </div>
                <span class="transition-transform duration-300 group-open:rotate-180 shrink-0">
                    <flux:icon icon="chevron-down" variant="micro" class="text-zinc-400" />
                </span>
            </summary>

            <div class="prose dark:prose-invert max-w-none prose-zinc dark:prose-zinc overflow-y-auto pr-2 lg:pr-4 border border-zinc-200 dark:border-zinc-800 rounded-b-lg p-4 lg:p-6 bg-white dark:bg-zinc-900 shadow-sm max-h-[60vh]">
                {!! $terms !!}
            </div>
        </details>

        <flux:separator class="my-4 md:my-6" />

        <div class="p-6 pt-0 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex items-start gap-3">
                <flux:icon icon="information-circle" class="text-zinc-400 mt-0.5 shrink-0" />
                <flux:text size="sm" class="max-w-md">
                    {{ __('Your account will be deleted if you do not accept these terms within one month of receiving the notification.') }}
                </flux:text>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <flux:modal.trigger name="decline-modal">
                    <flux:button variant="subtle" class="w-full sm:w-auto cursor-pointer">{{ __('Decline') }}</flux:button>
                </flux:modal.trigger>

                <flux:button variant="primary" wire:click="accept" class="w-full sm:w-auto lg:px-8 cursor-pointer">
                    {{ __('Accept and Continue') }}
                </flux:button>
            </div>
        </div>
    </flux:card>

    <flux:modal name="decline-modal" class="min-w-[22rem] space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Decline Terms of Service?') }}</flux:heading>
            <flux:description>
                {{ __('By declining, your account will be immediately anonymized. This action cannot be undone.') }}
            </flux:description>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="subtle">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="decline">{{ __('Delete account') }}</flux:button>
        </div>
    </flux:modal>
</div>
