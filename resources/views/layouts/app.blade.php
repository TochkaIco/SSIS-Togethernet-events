<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="flex min-h-screen flex-col transition-opacity opacity-100 duration-650 lg:grow starting:opacity-0">
        {{ $slot }}

        <livewire:feedback-modal />

        <div class="fixed bottom-5 right-5 z-50">
            <flux:tooltip :content="__('Feedback')">
                <button type="button"
                        class="rounded-full p-2 bg-white dark:bg-white/10 text-primary shadow-md hover:shadow-lg transition-shadow cursor-pointer"
                        x-data="{}"
                        @click="$flux.modal('feedback-modal').show()">
                    <flux:icon icon="chat-bubble-left" class="size-8 text-accent-content/65" />
                </button>
            </flux:tooltip>
        </div>
        <x-signature /> {{-- Togethernet and Sasha Romanova's (original developer) copyrights --}}
    </flux:main>
</x-layouts::app.sidebar>
