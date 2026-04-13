<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Delete account') }}</flux:heading>
        <flux:subheading>{{ __('Delete your account and all of its resources') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button class="cursor-pointer" variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Delete account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal
        name="confirm-user-deletion"
        :show="$errors->isNotEmpty()"
        focusable
        class="max-w-lg"
    >
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter "DELETE" to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="confirmation" :label="__('Confirmation')" placeholder="DELETE" />

            <div
                class="flex justify-end space-x-2 rtl:space-x-reverse"
                x-data="{ canDelete: false, timer: 3 }"
                x-on:modal-show.window="canDelete = false; timer = 3; let interval = setInterval(() => { if(timer > 0) { timer-- } else { canDelete = true; clearInterval(interval) } }, 1000)"
            >
                <flux:modal.close>
                    <flux:button variant="filled" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="danger"
                    x-bind:disabled="!canDelete"
                    x-bind:class="!canDelete && 'opacity-50 grayscale'"
                >
                    <span x-show="canDelete">{{ __('Delete account') }}</span>
                    <span x-show="!canDelete">{{ __('Wait') }} (<span x-text="timer"></span>s)</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
