<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="transition-opacity opacity-100 duration-650 lg:grow starting:opacity-0">
        {{ $slot }}
        <x-signature />
    </flux:main>
</x-layouts::app.sidebar>
