<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="flex min-h-screen flex-col transition-opacity opacity-100 duration-650 lg:grow starting:opacity-0">
        {{ $slot }}

        <livewire:feedback-modal />

        <div class="fixed bottom-5 right-5 z-50">
            <div class="inline-flex items-center" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                <div class="inline-flex items-center" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button type="button"
                            class="relative flex items-center rounded-full bg-white/10 text-primary shadow-md hover:shadow-lg transition-all duration-300 ease-in-out cursor-pointer h-12"
                            :class="open ? 'px-4 gap-3' : 'w-12 justify-center'"
                            aria-label="feedback-button"
                            @click="$flux.modal('feedback-modal').show()">

                        <span class="whitespace-nowrap text-sm font-medium transition-all duration-300 overflow-hidden"
                              :class="open ? 'opacity-100 max-w-[100px]' : 'opacity-0 max-w-0 pointer-events-none'">
                            Feedback
                        </span>

                        <flux:icon icon="chat-bubble-left" class="size-8 text-accent-content/65 shrink-0" />
                    </button>
                </div>
            </div>
        </div>
        <x-signature /> {{-- Togethernet and Sasha Romanova's (original developer) copyrights --}}
    </flux:main>
</x-layouts::app.sidebar>
