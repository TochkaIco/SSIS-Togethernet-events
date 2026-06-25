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
                    <flux:sidebar.item icon="clipboard-document-list" href="{{ config('app.dev_info.jira_url') }}" target="_blank">
                        {{ __('Jira') }}
                    </flux:sidebar.item>
                @endif

                @if(config('app.dev_info.sentry_url'))
                    <flux:sidebar.item icon="chart-bar-square" href="{{ config('app.dev_info.sentry_url') }}" target="_blank">
                        {{ __('Sentry') }}
                    </flux:sidebar.item>
                @endif

                @if(config('app.dev_info.argocd_url'))
                    <flux:sidebar.item icon="arrow-path" href="{{ config('app.dev_info.argocd_url') }}" target="_blank">
                        {{ __('ArgoCD') }}
                    </flux:sidebar.item>
                @endif
            </nav>
        </aside>

        <!-- Mobile sub-nav -->
        <div class="space-y-4 lg:hidden">
            <div>
                <flux:heading size="sm" class="mb-2 text-zinc-500 uppercase tracking-wider">{{ __('Documentation') }}</flux:heading>
                <flux:select x-on:change="Livewire.navigate('/docs/' + $event.target.value)">
                    @foreach($pages as $slug => $pageTitle)
                        <option value="{{ $slug }}" @selected($currentPage === $slug)>{{ $pageTitle }}</option>
                    @endforeach
                </flux:select>
            </div>

            @php
                $devLinks = array_filter([
                    'repo' => config('app.dev_info.repo'),
                    'openshift_url' => config('app.dev_info.openshift_url'),
                    'jira_url' => config('app.dev_info.jira_url'),
                    'sentry_url' => config('app.dev_info.sentry_url'),
                    'argocd_url' => config('app.dev_info.argocd_url'),
                ]);
            @endphp

            @if(!empty($devLinks))
                <div>
                    <flux:heading size="sm" class="mb-2 text-zinc-500 uppercase tracking-wider">{{ __('External Resources') }}</flux:heading>

                    <flux:select x-on:change="if($event.target.value) window.open($event.target.value, '_blank')">
                        <option value="" selected disabled hidden>{{ __('Select a resource...') }}</option>
                        @foreach([
                            'repo' => 'Repository',
                            'openshift_url' => 'Openshift',
                            'jira_url' => 'Jira',
                            'sentry_url' => 'Sentry',
                            'argocd_url' => 'ArgoCD',
                        ] as $key => $label)
                            @if(isset($devLinks[$key]))
                                <option value="{{ $devLinks[$key] }}">{{ __($label) }}</option>
                            @endif
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>

        <!-- Content -->
        <main class="flex-1 min-w-0">
            <div class="prose dark:prose-invert max-w-none">
                {!! $content !!}
            </div>
        </main>
    </div>
</div>
