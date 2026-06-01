<div class="flex flex-col flex-1 w-full px-4 py-6 mx-auto max-w-8xl lg:px-8">
    <div class="flex flex-col gap-8 lg:flex-row">
        <!-- Sidebar for documentation navigation (Desktop) -->
        <aside class="hidden w-64 shrink-0 lg:block">
            <nav class="space-y-1">
                <flux:heading size="sm" class="mb-4 text-zinc-500 uppercase tracking-wider">{{ __('Documentation') }}</flux:heading>
                @foreach($pages as $slug => $pageTitle)
                    <flux:sidebar.item
                        :href="route('docs', $slug)"
                        :current="$currentPage === $slug"
                        wire:navigate
                    >
                        {{ $pageTitle }}
                    </flux:sidebar.item>
                @endforeach

                <flux:heading size="sm" class="mt-8 mb-4 text-zinc-500 uppercase tracking-wider">{{ __('External Resources') }}</flux:heading>

                @if(config('app.dev_info.repo'))
                    <flux:sidebar.item icon="folder-git-2" href="{{ config('app.dev_info.repo') }}" target="_blank">
                        {{ __('Repository') }}
                    </flux:sidebar.item>
                @endif

                @if(config('app.dev_info.openshift_url'))
                    <flux:sidebar.item icon="cloud" href="{{ config('app.dev_info.openshift_url') }}" target="_blank">
                        {{ __('Openshift') }}
                    </flux:sidebar.item>
                @endif

                @if(config('app.dev_info.jira_url'))
                    <flux:sidebar.item icon="chart-bar-square" href="{{ config('app.dev_info.jira_url') }}" target="_blank">
                        {{ __('Jira') }}
                    </flux:sidebar.item>
                @endif

                @if(config('app.dev_info.sentry_url'))
                    <flux:sidebar.item icon="chart-bar-square" href="{{ config('app.dev_info.sentry_url') }}" target="_blank">
                        {{ __('Sentry') }}
                    </flux:sidebar.item>
                @endif
            </nav>
        </aside>

        <!-- Mobile sub-nav -->
        <div class="lg:hidden">
            <flux:select x-on:change="Livewire.navigate('/docs/' + $event.target.value)">
                @foreach($pages as $slug => $pageTitle)
                    <option value="{{ $slug }}" @selected($currentPage === $slug)>{{ $pageTitle }}</option>
                @endforeach
            </flux:select>
        </div>

        <!-- Content -->
        <main class="flex-1 min-w-0">
            <div class="prose dark:prose-invert max-w-none">
                {!! $content !!}
            </div>
        </main>
    </div>
</div>
