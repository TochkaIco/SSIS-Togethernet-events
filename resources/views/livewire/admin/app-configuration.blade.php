<div class="mx-auto space-y-6">
    <flux:text class="text-4xl text-accent-content font-medium">{{ __('App Configuration') }}</flux:text>
    <flux:separator />
    <flux:checkbox
        wire:model.live="allowExternal"
        label="{{ __('Allow external email domains') }}"
        description="If unchecked, only accounts from {{ config('services.google.hd') }} can login."
    />
</div>
