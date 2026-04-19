<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="flex min-h-screen flex-col transition-opacity opacity-100 duration-650 lg:grow starting:opacity-0">
        {{ $slot }}

        <livewire:feedback-modal />

        <x-signature /> {{-- Togethernet and Fedor Romanov's (original developer) copyrights --}}
    </flux:main>
</x-layouts::app.sidebar>
