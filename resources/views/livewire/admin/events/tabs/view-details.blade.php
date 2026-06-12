<div>
    <div class="flex flex-col md:flex-row md:space-x-3 space-y-3 md:space-y-0 mb-6">
        @if($event->event_type !== \App\EventType::QR_TAG)
            <div class="flex items-center gap-2">
                <span class="font-medium text-muted-foreground">{{ __('Number of Seats:') }}</span>
                <flux:badge color="orange" size="sm">
                    {{ $event->num_of_seats }}
                </flux:badge>
            </div>
        @endif

        @if($event->paid_entry===1)
            <div class="flex items-center gap-2">
                <span class="font-medium text-muted-foreground">{{ __('Entrance Fee:') }}</span>
                <flux:badge color="orange" size="sm">
                    {{ $event->entry_fee }} kr
                </flux:badge>
            </div>
        @else
            <flux:badge color="orange">
                {{ __('This event is free') }}
            </flux:badge>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Event Stats Overview --}}
        <flux:card class="transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-bold mb-4">{{ __('Attendance Overview') }}</h2>

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <span class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Registrations') }}</span>
                        <span class="text-2xl font-bold">{{ $this->stats['registrations'] }}</span>
                    </div>
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <span class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Attendance') }}</span>
                        <span class="text-2xl font-bold">{{ $this->stats['attendance'] }}</span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium">{{ __('Attendance Rate') }}</span>
                        <span class="text-sm font-bold text-orange-500">{{ $this->stats['attendance_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2.5">
                        <div class="bg-orange-500 h-2.5 rounded-full" style="width: {{ $this->stats['attendance_rate'] }}%"></div>
                    </div>
                </div>

                @if($event->num_of_seats > 0)
                    @php
                        $fillRate = round(($this->stats['registrations'] / $event->num_of_seats) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium">{{ __('Capacity Usage') }}</span>
                            <span class="text-sm font-bold text-blue-500">{{ $fillRate }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2.5">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ min(100, $fillRate) }}%"></div>
                        </div>
                        <p class="text-[10px] text-zinc-500 mt-1 text-right">
                            {{ $this->stats['registrations'] }} / {{ $event->num_of_seats }} {{ __('seats taken') }}
                        </p>
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Class Distribution Chart --}}
        <flux:card class="transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-bold mb-4">{{ __('Class Breakdown') }}</h2>
            @if($this->stats['registrations'] > 0)
                <div
                    x-data="{
                        init() {
                            const isMobile = window.innerWidth < 768;
                            const dist = @js($this->stats['class_distribution']);
                            new Chart(this.$refs.eventClassChart, {
                                type: 'doughnut',
                                data: {
                                    labels: dist.labels,
                                    datasets: [{
                                        data: dist.data,
                                        backgroundColor: dist.colors,
                                        borderWidth: 2,
                                        hoverOffset: 5
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '0%',
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                usePointStyle: true,
                                                padding: isMobile ? 10 : 20,
                                                font: { size: isMobile ? 10 : 12 }
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    const label = context.label || '';
                                                    const value = context.raw || 0;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = Math.round((value / total) * 100);
                                                    return `${label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }"
                    class="h-64"
                >
                    <canvas x-ref="eventClassChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
                    <flux:icon.users class="size-12 mb-4 opacity-20" />
                    <p>{{ __('No participants yet.') }}</p>
                </div>
            @endif
        </flux:card>
    </div>

    @if($event->image_path)
        <div class="rounded-lg overflow-hidden mb-6 group relative">
            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
        </div>
    @endif

    @if($event->one_hour_periods)
        <flux:badge>{{ __('Date') }}:<span class="ml-2 text-orange-300">{{ $event->event_starts_at->format('M j, Y') }}</span></flux:badge>
    @else
        <div class="mt-2 flex flex-col md:flex-row space-y-3 md:gap-x-3 md:space-y-0 md:items-center text-sm">
            <flux:badge>{{ __('Starts at ') }}<span class="ml-2 text-orange-300">{{ $event->event_starts_at->format('M j, Y, h:i') }}</span></flux:badge>
            <flux:badge>{{ __('Ends at ') }}<span class="ml-2 text-orange-300">{{ $event->event_ends_at->format('M j, Y, h:i') }}</span></flux:badge>
        </div>
    @endif

    <div class="mt-6 flex flex-col md:flex-row space-y-3 md:gap-x-3 md:space-y-0 md:items-center text-sm">
        <span>{{ __('Created') }} {{ $event->created_at->diffForHumans() }}</span>
        @if($event->created_at != $event->updated_at)
            <span>{{ __('Updated') }} {{ $event->updated_at->diffForHumans() }}</span>
        @endif
    </div>

    @if($event->description)
        <flux:card class="mt-6">
            <div class="prose prose-zinc dark:prose-invert max-w-none">
                {!! $event->formattedDescription !!}
            </div>
        </flux:card>
    @endif

    @if($event->periods()->count() > 1)
        <h3 class="font-bold mt-6 mb-3">{{ __('Event Schedule') }}</h3>
        <div class="flex flex-col">
            @foreach($event->eventPeriods() as $item)
                @if($item->type === 'period')
                    {{-- Period Row --}}
                    <flux:badge class="p-1 border flex items-center justify-between">
                        <span class="font-medium px-2 italic">{{ __('Period') }} {{ $item->number }}</span>

                        <span class="border-l-4 border-l-orange-300 border border-orange-300 p-1 font-bold tracking-wider rounded text-sm bg-orange-300/20">
                            {{ $item->label }}
                        </span>
                    </flux:badge>
                @else
                    {{-- Break Row --}}
                    <div class="flex flex-col items-center justify-center">
                        <flux:icon icon="arrow-down" />
                        <div class="bg-accent-foreground px-3 text-[12px] font-bold uppercase tracking-widest text-muted-foreground border border-accent-content rounded-full shadow-sm">
                            {{ $item->label }}
                        </div>
                        <flux:icon icon="arrow-down" />
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if($event->links && count($event->links))
        <div class="mt-8">
            <h3 class="font-bold text-xl mb-4">{{ __('Links') }}</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($event->links as $link)
                    <flux:card :href="$link" class="transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl hover:text-orange-300 min-w-0" size="sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <flux:icon.link class="size-4 text-zinc-400 shrink-0" />
                            <span class="truncate text-sm font-medium min-w-0">{{ $link }}</span>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @endif
</div>
