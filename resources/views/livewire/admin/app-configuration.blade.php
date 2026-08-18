<div class="mx-auto space-y-6">
    <flux:text class="text-4xl text-accent-content font-medium">{{ __('App Configuration') }}</flux:text>
    <flux:separator />

    <flux:checkbox
        wire:model.live="useElevkarAuth"
        label="{{ __('Use Elevkår Auth') }}"
        description="{{ __('Use Elevkår authentication for this website.') }}"
    />

    <flux:checkbox
        wire:model.live="allowExternal"
        label="{{ __('Allow external email domains') }}"
        description="{{ __('login_restriction', ['hd' => config('services.google.hd')]) }}"
    />

    <flux:checkbox
        wire:model.live="automatedWaitingListMove"
        label="{{ __('Automated waiting list move') }}"
        description="{{ __('Automatically move people from the waiting list when a spot becomes available.') }}"
    />

    <flux:separator />

    <flux:field>
        <flux:label>{{ __('Pant Swish Number') }}</flux:label>
        <flux:input wire:model.live.debounce.500ms="pantSwishNumber" placeholder="e.g. 1234567890" />
        <flux:description>{{ __('The Swish account number to which pant money should be sent.') }}</flux:description>
    </flux:field>
</div>
