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
        {{-- Registration Timeline Chart --}}
        <flux:card class="transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
            <h2 class="text-lg font-bold mb-4">{{ __('Registration Timeline') }}</h2>

            @if($this->stats['registrations'] > 0)
                <div
                    x-data="{
                        init() {
                            const times = @js($this->stats['registration_timeline']['times']);
                            const eventStart = @js($this->stats['registration_timeline']['event_start']);
                            const eventCreated = @js($this->stats['registration_timeline']['event_created']);
                            const displayStartsAt = @js($this->stats['registration_timeline']['display_starts_at']);
                            const eventEndsAt = @js($this->stats['registration_timeline']['event_ends_at']);
                            const now = Date.now();

                            const dataPoints = [];
                            
                            // Start at 0 on displayStartsAt
                            dataPoints.push({ x: displayStartsAt, y: 0 });

                            let count = 0;
                            times.forEach(t => {
                                count++;
                                dataPoints.push({ x: t, y: count });
                            });

                            // Add a point for eventEndsAt to keep the line flat to the end of the chart
                            const lastTime = times[times.length - 1] || displayStartsAt;
                            const currentEnd = eventEndsAt;
                            
                            if (currentEnd > lastTime) {
                                dataPoints.push({ x: currentEnd, y: count });
                            }

                            const minX = displayStartsAt;
                            const maxX = eventEndsAt;

                            new Chart(this.$refs.registrationTimelineChart, {
                                type: 'line',
                                data: {
                                    datasets: [{
                                        label: '{{ __('Registrations') }}',
                                        data: dataPoints,
                                        borderColor: '#f97316',
                                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                        borderWidth: 2,
                                        fill: true,
                                        stepped: true,
                                        pointRadius: times.length > 50 ? 0 : 3,
                                        pointHoverRadius: 5,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: {
                                            type: 'linear',
                                            min: minX,
                                            max: maxX,
                                            ticks: {
                                                callback: function(value) {
                                                    const date = new Date(value);
                                                    return date.toLocaleDateString(undefined, { 
                                                        month: 'short', 
                                                        day: 'numeric'
                                                    });
                                                },
                                                maxRotation: 45,
                                                minRotation: 0,
                                            },
                                            title: {
                                                display: true,
                                                text: '{{ __('Date') }}'
                                            }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                precision: 0
                                            },
                                            title: {
                                                display: true,
                                                text: '{{ __('Total Participants') }}'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                title: function(context) {
                                                    const date = new Date(context[0].raw.x);
                                                    return date.toLocaleString();
                                                },
                                                label: function(context) {
                                                    return `{{ __('Registrations') }}: ${context.raw.y}`;
                                                }
                                            }
                                        }
                                    }
                                },
                                plugins: [{
                                    id: 'verticalLine',
                                    afterDraw: (chart) => {
                                        const xAxis = chart.scales.x;
                                        const yAxis = chart.scales.y;
                                        if (eventStart >= xAxis.min && eventStart <= xAxis.max) {
                                            const xPixel = xAxis.getPixelForValue(eventStart);
                                            const ctx = chart.ctx;
                                            ctx.save();
                                            ctx.beginPath();
                                            ctx.moveTo(xPixel, yAxis.top);
                                            ctx.lineTo(xPixel, yAxis.bottom);
                                            ctx.lineWidth = 2;
                                            ctx.strokeStyle = '#ef4444';
                                            ctx.setLineDash([5, 5]);
                                            ctx.stroke();
                                            ctx.restore();
                                        }
                                    }
                                }]
                            });
                        }
                    }"
                    class="h-64"
                >
                    <canvas x-ref="registrationTimelineChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
                    <flux:icon.users class="size-12 mb-4 opacity-20" />
                    <p>{{ __('No participants yet.') }}</p>
                </div>
            @endif
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
            <flux:badge>{{ __('Starts at ') }}<span class="ml-2 text-orange-300">{{ $event->event_starts_at->format('M j, Y, H:i') }}</span></flux:badge>
            <flux:badge>{{ __('Ends at ') }}<span class="ml-2 text-orange-300">{{ $event->event_ends_at->format('M j, Y, H:i') }}</span></flux:badge>
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
