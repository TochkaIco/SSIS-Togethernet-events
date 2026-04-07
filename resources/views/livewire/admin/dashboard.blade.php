<div class="p-6">
    <div class="mb-6 flex flex-col gap-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('home') }}" icon="home">{{ __('Home') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" icon="chart-pie">{{ __('Dashboard Overview') }}</flux:breadcrumbs>
        </flux:breadcrumbs>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Users --}}
        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <flux:icon.users variant="outline" class="size-6" />
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total Users') }}</span>
            </div>
            <div class="text-3xl mt-1 font-bold">{{ $this->totalUsers }}</div>
        </flux:card>

        {{-- Active Events --}}
        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <flux:icon.calendar variant="outline" class="size-6" />
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Active Events') }}</span>
            </div>
            <div class="text-3xl mt-1 font-bold">{{ $this->activeEventsCount }}</div>
        </flux:card>

        {{-- Upcoming Events --}}
        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <flux:icon.clock variant="outline" class="size-6" />
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Upcoming Events') }}</span>
            </div>
            <div class="text-3xl mt-1 font-bold">{{ $this->upcomingEventsCount }}</div>
        </flux:card>

        {{-- Kiosk Revenue --}}
        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <flux:icon.banknotes variant="outline" class="size-6" />
                <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kiosk Revenue') }}</span>
            </div>
            <div class="text-3xl mt-1 font-bold">{{ $this->totalRevenue }} kr</div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Latest Event Stats --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">{{ __('Latest Event Stats') }}</h2>
                @if($this->latestEvent)
                    <flux:badge color="orange" variant="outline">{{ $this->latestEvent->title }}</flux:badge>
                @endif
            </div>

            @if($this->latestEvent)
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <span class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Registrations') }}</span>
                            <span class="text-2xl font-bold">{{ $this->latestEventStats['registrations'] }}</span>
                        </div>
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <span class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Attendance') }}</span>
                            <span class="text-2xl font-bold">{{ $this->latestEventStats['attendance'] }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium">{{ __('Attendance Rate') }}</span>
                            <span class="text-sm font-bold text-orange-500">{{ $this->latestEventStats['attendance_rate'] }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2.5">
                            <div class="bg-orange-500 h-2.5 rounded-full" style="width: {{ $this->latestEventStats['attendance_rate'] }}%"></div>
                        </div>
                    </div>

                    <flux:button href="{{ route('admin.event.show', $this->latestEvent) }}" variant="ghost" size="sm" class="w-full">
                        {{ __('View Event Details') }}
                    </flux:button>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
                    <flux:icon.calendar class="size-12 mb-4 opacity-20" />
                    <p>{{ __('No event data available.') }}</p>
                </div>
            @endif
        </flux:card>

        {{-- Quick Links --}}
        <flux:card>
            <h2 class="text-lg font-bold mb-4">{{ __('Quick Actions') }}</h2>
            <div class="grow gap-3">
                @can('create articles')
                    <flux:button x-on:click="$flux.modal('create-event').show()" icon="plus" variant="subtle" class="justify-start cursor-pointer">
                        {{ __('Create Event') }}
                    </flux:button>
                @endcan
                @can('view articles')
                        <flux:button href="{{ route('admin.events') }}" icon="calendar" variant="subtle" class="justify-start">
                            {{ __('Manage Events') }}
                        </flux:button>
                @endcan
                @can('manage users')
                        <flux:button href="{{ route('admin.users') }}" icon="users" variant="subtle" class="justify-start">
                            {{ __('Manage Users') }}
                        </flux:button>
                @endcan
            </div>

            @can('dev')
                <div class="mt-8">
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">{{ __('System Health') }}</h3>
                    <div class="space-y-4">
                        {{-- Database & PHP --}}
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <flux:icon.server variant="outline" class="size-4 text-zinc-400" />
                                <span>{{ __('Database & PHP') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge color="green" size="sm" inset="top bottom">{{ strtoupper($this->systemStats['db_host']) }}</flux:badge>
                                <span class="font-mono text-xs">{{ phpversion() }}</span>
                            </div>
                        </div>

                        {{-- Environment --}}
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <flux:icon.cpu-chip variant="outline" class="size-4 text-zinc-400" />
                                <span>{{ __('Environment') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge :color="$this->systemStats['env'] === 'production' ? 'red' : 'blue'" size="sm" inset="top bottom">{{ strtoupper($this->systemStats['env']) }}</flux:badge>
                                @if($this->systemStats['debug'])
                                    <flux:badge color="yellow" size="sm" inset="top bottom">{{ __('DEBUG') }}</flux:badge>
                                @endif
                            </div>
                        </div>

                        {{-- Laravel Version --}}
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <flux:icon.check-badge variant="outline" class="size-4 text-zinc-400" />
                                <span>{{ __('Laravel Version') }}</span>
                            </div>
                            <span class="font-mono text-xs">v{{ $this->systemStats['laravel_version'] }}</span>
                        </div>

                        {{-- Failed Jobs --}}
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <flux:icon.exclamation-triangle variant="outline" class="size-4 text-zinc-400" />
                                <span>{{ __('Failed Jobs') }}</span>
                            </div>
                            @if($this->systemStats['failed_jobs'] > 0)
                                <flux:badge color="red" size="sm" inset="top bottom">{{ $this->systemStats['failed_jobs'] }}</flux:badge>
                            @else
                                <flux:badge color="green" size="sm" inset="top bottom">0</flux:badge>
                            @endif
                        </div>

                        {{-- Storage Usage --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <flux:icon.circle-stack variant="outline" class="size-4 text-zinc-400" />
                                    <span>{{ __('Storage Usage (Public)') }}</span>
                                </div>
                                <span class="text-zinc-500">{{ $this->systemStats['disk_label'] }}</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="{
                                    'bg-green-500': {{ $this->systemStats['disk_percentage'] }} < 70,
                                    'bg-yellow-500': {{ $this->systemStats['disk_percentage'] }} >= 70 && {{ $this->systemStats['disk_percentage'] }} < 90,
                                    'bg-red-500': {{ $this->systemStats['disk_percentage'] }} >= 90
                                }"
                                    style="width: {{ $this->systemStats['disk_percentage'] }}%"
                                ></div>
                            </div>
                        </div>

                        {{-- Timezone --}}
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-zinc-100 dark:border-zinc-800 text-zinc-500">
                            <span>{{ __('Timezone') }}</span>
                            <span>{{ $this->systemStats['timezone'] }}</span>
                        </div>
                    </div>
                </div>
            @endcan
            @cannot('dev')
                <div class="mt-8">
                    {{-- Placeholder Header --}}
                    <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">{{ __('System Health') }}</h3>

                    <div class="space-y-4">
                        {{-- Generic Row 1 --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="size-4 rounded-full bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                                <div class="h-3 w-32 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                            </div>
                            <div class="h-5 w-12 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                        </div>

                        {{-- Generic Row 2 --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="size-4 rounded-full bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                                <div class="h-3 w-24 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                            </div>
                            <div class="h-5 w-16 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                        </div>

                        {{-- Generic Row 3 --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="size-4 rounded-full bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                                <div class="h-3 w-28 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                            </div>
                            <div class="h-3 w-8 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                        </div>

                        {{-- Progress Bar Placeholder --}}
                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between">
                                <div class="h-3 w-20 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                                <div class="h-3 w-12 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"></div>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full bg-zinc-200 dark:bg-zinc-700 w-1/4 animate-pulse"></div>
                            </div>
                        </div>

                        {{-- Footer Placeholder --}}
                        <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="h-3 w-16 bg-zinc-50 dark:bg-zinc-900 rounded animate-pulse"></div>
                            <div class="h-3 w-20 bg-zinc-50 dark:bg-zinc-900 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>
            @endcannot
        </flux:card>

        {{-- Attendance History Chart --}}
        <flux:card>
            <h2 class="text-lg font-bold mb-4">{{ __('Attendance Trends') }}</h2>
            <div
                x-data="{
                    init() {
                        new Chart(this.$refs.attendanceChart, {
                            type: 'bar',
                            data: {
                                labels: @js($this->attendanceHistory['labels']),
                                datasets: [
                                    {
                                        label: '{{ __('Registrations') }}',
                                        data: @js($this->attendanceHistory['registrations']),
                                        backgroundColor: '#fb923c',
                                        borderRadius: 6,
                                    },
                                    {
                                        label: '{{ __('Attendance') }}',
                                        data: @js($this->attendanceHistory['attendance']),
                                        backgroundColor: '#f87171',
                                        borderRadius: 6,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { position: 'bottom' } },
                                scales: {
                                    y: { beginAtZero: true, grid: { display: false } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }
                }"
                class="h-64"
            >
                <canvas x-ref="attendanceChart"></canvas>
            </div>
        </flux:card>

        {{-- Kiosk Revenue Chart --}}
        <flux:card>
            <h2 class="text-lg font-bold mb-4">{{ __('Kiosk Revenue Trends') }}</h2>
            <div
                x-data="{
                    init() {
                        new Chart(this.$refs.revenueChart, {
                            type: 'line',
                            data: {
                                labels: @js($this->monthlyRevenue['labels']),
                                datasets: [{
                                    label: '{{ __('Monthly Revenue (kr)') }}',
                                    data: @js($this->monthlyRevenue['data']),
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }
                }"
                class="h-64"
            >
                <canvas x-ref="revenueChart"></canvas>
            </div>
        </flux:card>

        {{-- User Growth Chart --}}
        <flux:card>
            <h2 class="text-lg font-bold mb-4">{{ __('User Registrations (Last 30 days)') }}</h2>
            <div
                x-data="{
                    init() {
                        new Chart(this.$refs.growthChart, {
                            type: 'line',
                            data: {
                                labels: @js($this->userGrowth['labels']),
                                datasets: [{
                                    label: '{{ __('New Users') }}',
                                    data: @js($this->userGrowth['data']),
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                                    x: { ticks: { maxTicksLimit: 7 }, grid: { display: false } }
                                }
                            }
                        });
                    }
                }"
                class="h-64"
            >
                <canvas x-ref="growthChart"></canvas>
            </div>
        </flux:card>
    </div>

    <x-admin.event.modal />
</div>
