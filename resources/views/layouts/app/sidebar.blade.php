<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" :href="route('home')" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group class="grid">
                    <flux:sidebar.item icon="home" :href="route('home')" :current="request()->routeIs('home*')">
                        {{ __('Home') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="layout-grid" :href="route('events')" :current="request()->routeIs('event*')">
                        {{ __('Events') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="question-mark-circle" :href="route('faq')" :current="request()->routeIs('faq*')">
                        {{ __('FAQ') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            @hasanyrole('tog-member|admin|super-admin|maintainer')
                <flux:navmenu.separator />

                <flux:sidebar.nav>
                    <flux:sidebar.group :heading="__('Admin Panel')" class="grid">
                        <flux:sidebar.item icon="chart-pie" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard*')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        @can('view articles')
                            <flux:sidebar.item icon="layout-grid" :href="route('admin.events')" :current="request()->routeIs('admin.events')" wire:navigate>
                                {{ __('Events') }}
                            </flux:sidebar.item>

                            @if(request()->routeIs('admin.event.show') && $event = request()->route('event'))
                                <flux:sidebar.group :heading="$event->title" expandable expanded class="ml-4">
                                    <flux:sidebar.item :href="route('admin.event.show', ['event' => $event, 'tab' => 'view'])" :current="request('tab', 'view') === 'view'" wire:navigate>
                                        {{ __('View') }}
                                    </flux:sidebar.item>
                                    @can('manage users')
                                        <flux:sidebar.item :href="route('admin.event.show', ['event' => $event, 'tab' => 'participants'])" :current="request('tab') === 'participants'" wire:navigate>
                                            {{ __('Participants') }}
                                        </flux:sidebar.item>
                                        <flux:sidebar.item :href="route('admin.event.show', ['event' => $event, 'tab' => 'waiting'])" :current="request('tab') === 'waiting'" wire:navigate>
                                            {{ __('Waiting List') }}
                                        </flux:sidebar.item>
                                    @endcan
                                    @can('manage kiosk')
                                        <flux:sidebar.item :href="route('admin.event.show', ['event' => $event, 'tab' => 'kiosk'])" :current="request('tab') === 'kiosk'" wire:navigate>
                                            {{ __('Kiosk') }}
                                        </flux:sidebar.item>
                                    @endcan
                                </flux:sidebar.group>
                            @endif

                            <flux:sidebar.item icon="calendar" :href="route('admin.meetings.index')" :current="request()->routeIs('admin.meetings.index')" wire:navigate>
                                {{ __('Meetings') }}
                            </flux:sidebar.item>

                            @if((request()->routeIs('admin.meetings.show') || request()->routeIs('admin.meetings.protocol')) && $meeting = request()->route('meeting'))
                                <flux:sidebar.group :heading="is_object($meeting) ? Str::limit($meeting->title, 20) : __('Meeting')" expandable expanded class="ml-4">
                                    <flux:sidebar.item :href="route('admin.meetings.show', $meeting)" :current="request()->routeIs('admin.meetings.show')" wire:navigate>
                                        {{ __('View Details') }}
                                    </flux:sidebar.item>
                                    <flux:sidebar.item :href="route('admin.meetings.protocol', $meeting)" :current="request()->routeIs('admin.meetings.protocol')" wire:navigate>
                                        {{ __('Protocol') }}
                                    </flux:sidebar.item>
                                </flux:sidebar.group>
                            @endif
                            @can('manage users')
                                <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.user*')" wire:navigate>
                                    {{ __('Users') }}
                                </flux:sidebar.item>
                            @endcan
                            @haspermission('configure pages')
                                <flux:sidebar.item icon="wrench-screwdriver" :href="route('admin.app.config')" :current="request()->routeIs('admin.app.config*')" wire:navigate>
                                    {{ __('Configuration') }}
                                </flux:sidebar.item>
                            @endhaspermission
                            @hasanyrole(['admin', 'super-admin', 'maintainer'])
                                <flux:sidebar.item icon="book-open" :href="route('admin.feedback')" :current="request()->routeIs('admin.feedback*')" wire:navigate>
                                    {{ __('View Feedback') }}
                                </flux:sidebar.item>
                            @endhasanyrole
                        @endcan
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endhasanyrole

            @can('dev')
                <flux:navmenu.separator />

                <flux:sidebar.nav>
                    <flux:sidebar.group :heading="__('Dev')" class="grid">
                        @can('impersonate users')
                            <flux:sidebar.item icon="user" href="{{ route('admin.impersonation-page') }}" :current="request()->routeIs('admin.impersonation-page*')" wire:navigate>
                                {{ __('User Impersonation') }}
                            </flux:sidebar.item>
                        @endcan

                        <flux:sidebar.item icon="command-line" href="/admin/pulse" target="_blank">
                            {{ __('Dev Monitor') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="folder-git-2" href="{{ config('app.dev_info.repo') }}" target="_blank">
                            {{ __('Repository') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="folder-git-2" href="{{ config('app.dev_info.openshift_url') }}" target="_blank">
                            {{ __('Openshift') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="chart-bar-square" href="{{ config('app.dev_info.jira_url') }}" target="_blank">
                            {{ __('Jira') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endcan

            <flux:spacer />

            <flux:sidebar.item icon="book-open" class="cursor-pointer" x-on:click="$flux.modal('feedback-modal').show()">{{ __('Give Feedback') }}</flux:sidebar.item>

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @endauth
            @guest
                <flux:button variant="primary" :href="route('login')" icon="user-plus">{{ __('Sign In') }}</flux:button>
            @endguest
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :avatar="auth()->user()->profile_picture"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :src="auth()->user()->profile_picture"
                                        :name="auth()->user()->name"
                                        :initials="auth()->user()->initials()"
                                    />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            @if(app('impersonate')->isImpersonating())
                                <flux:menu.item :href="route('impersonate.leave')" icon="arrow-down-left" class="text-red-500">
                                    {{ __('Stop Impersonating') }}
                                </flux:menu.item>
                                <flux:menu.separator />
                            @endif
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast />
        @endpersist

        @fluxScripts
        @stack('scripts')
    </body>
</html>
