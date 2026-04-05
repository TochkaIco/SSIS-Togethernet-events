<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <div class="space-y-6">
        <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
            <div class="space-y-3">
                <flux:radio.group class="cursor-pointer" x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>

                <flux:radio.group
                    class="cursor-pointer"
                    variant="segmented"
                    wire:model="locale"
                    x-on:change="$wire.updateLocale($event.target.value)"
                >
                    <flux:radio value="sv">{{ __('Swedish') }}</flux:radio>
                    <flux:radio value="en">{{ __('English') }}</flux:radio>
                </flux:radio.group>
            </div>
        </x-settings.layout>
    </div>
</section>
