<div class="p-1 md:p-6">
    <div class="mb-6">
        <flux:button icon="chevron-left" variant="ghost" :href="route('admin.meetings.show', $meeting)" wire:navigate size="sm" class="-ml-2 mb-2">
            {{ __('Back to Meeting') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Edit Protocol') }}</flux:heading>
        <flux:subheading>{{ $meeting->title }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" label="{{ __('Title') }}" required />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input type="datetime-local" wire:model="meeting_starts_at" label="{{ __('Starts At') }}" required />
            <flux:input type="datetime-local" wire:model="meeting_ends_at" label="{{ __('Ends At') }}" />
        </div>

        <div class="space-y-2">
            <flux:label>{{ __('Protocol (Notes)') }}</flux:label>
            <x-quill.editor model="description" id="protocol-editor" />
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <flux:button :href="route('admin.meetings.show', $meeting)" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Save Protocol') }}</flux:button>
        </div>
    </form>
</div>
