<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('events') }}" icon="layout-grid">{{ __('Events') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('event.show', $event) }}">{{ $event->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex items-center justify-between space-x-6">
            <h1 class="font-bold text-3xl">{{ $event->title }}</h1>

            <div class="flex items-end gap-4">
                @if(auth()->user())
                    @if(! $this->registration)
                        @if($event->canRegister())
                            @if($event->one_hour_periods)
                                <flux:select wire:model="period" placeholder="{{ __('Select Period') }}" class="min-w-48">
                                    <flux:select.option :value="null">{{ __('Any available period') }}</flux:select.option>
                                    @foreach($event->eventPeriods() as $item)
                                        @if($item->type === 'period')
                                            <flux:select.option value="{{ $item->id }}">{{ $item->label }} ({{ $event->seatsTaken($item->id) }}/{{ $event->num_of_seats }})</flux:select.option>
                                        @endif
                                    @endforeach
                                </flux:select>
                            @endif
                            <flux:button wire:click="registerUser({{ $event->id }})" variant="primary" class="cursor-pointer transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl">{{ __('Register') }}</flux:button>
                        @endif
                    @else
                        <div class="flex flex-col items-end gap-2">
                            <div class="flex gap-2 items-center">
                                @if($event->one_hour_periods && $this->registration->event_period_id)
                                    @php
                                        $periodLabel = $this->registration->eventPeriod?->label;
                                    @endphp
                                    <flux:badge color="orange" icon="clock" inset="top bottom">{{ __('Period') }}: {{ $periodLabel }}</flux:badge>
                                @endif

                                @if($this->registration->in_waitinglist)
                                    <flux:badge color="yellow" icon="clock">{{ __('On Waiting List') }}</flux:badge>
                                @else
                                    <flux:badge color="green" icon="check" class="cursor-default">{{ __('Registered as Participant') }}</flux:badge>
                                @endif
                            </div>

                            @if($event->canUnregister())
                                <flux:modal.trigger name="unregister-confirmation">
                                    <flux:button icon="x-mark" wire:click="confirmUnregister({{ $event->id }})" variant="danger" size="sm" class="cursor-pointer">{{ __('Unregister') }}</flux:button>
                                </flux:modal.trigger>
                            @endif
                        </div>
                    @endif
                @else
                    @if($event->canRegister())
                        <flux:button href="{{ route('login') }}" icon="user-plus" variant="primary" class="cursor-pointer transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl">{{ __('Login to register') }}</flux:button>
                    @endif
                @endif
            </div>
        </div>
    </div>
    <div class="md:min-w-5xl mt-6">
        <div class="flex flex-col md:flex-row md:space-x-3 space-y-3 md:space-y-0 mb-6">
            @if($event->event_type !== \App\EventType::QR_TAG)
                <div class="flex items-center gap-2">
                    <span class="font-medium text-muted-foreground">{{ __('Number of Seats:') }}</span>
                    <flux:badge color="orange" size="sm">
                        @if($event->one_hour_periods)
                            {{ $event->seatsTaken() }} / {{ $event->num_of_seats * ($event->one_hour_periods_number ?? 1) }}
                        @else
                            {{ $event->seatsTaken() }} / {{ $event->num_of_seats }}
                        @endif
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

        @if($event->image_path)
            <div class="rounded-lg overflow-hidden mb-6">
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
            </div>
        @else
            <div class="rounded-lg overflow-hidden mb-6">
                <img src="{{ asset('images/togethernet-feature.jpg') }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
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

        @if($event->event_type === \App\EventType::QR_TAG && $this->registration && !$this->registration->in_waitinglist)
            <flux:card class="mt-6 bg-orange-50 dark:bg-orange-950/20 border-orange-200 dark:border-orange-900">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="flex-1 space-y-4">
                        <div class="flex items-center gap-2">
                            <flux:icon.sparkles class="text-orange-500" />
                            <flux:heading size="lg">{{ __('QR Tag Game') }}</flux:heading>
                        </div>

                        @if($this->registration->qr_tag_tagged_at)
                            <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-lg border border-red-200 dark:border-red-800">
                                <p class="font-bold">{{ __('You have been tagged!') }}</p>
                                <p class="text-sm">
                                    {{ __('Tagged by :name at :time', [
                                        'name' => $this->registration->taggedBy->name,
                                        'time' => $this->registration->qr_tag_tagged_at->format('H:i')
                                    ]) }}
                                </p>
                            </div>
                        @elseif($this->registration->qr_tag_target_user_id)
                            @if($this->registration->qr_tag_target_user_id === auth()->id())
                                <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg border border-green-200 dark:border-green-800">
                                    <p class="font-bold">{{ __('Congratulations!') }}</p>
                                    <p class="text-sm">{{ __('You are the last one standing. You won the game!') }}</p>
                                </div>
                            @else
                                <div class="space-y-2">
                                    <flux:text>{{ __('Your current target is:') }}</flux:text>
                                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-sm">
                                        <flux:avatar :initials="$this->registration->targetUser->initials()" size="sm" />
                                        <span class="font-bold text-lg">{{ $this->registration->targetUser->name }}</span>
                                    </div>
                                    <flux:text size="sm" class="italic">{{ __('Find them and scan their QR code to tag them.') }}</flux:text>
                                </div>
                            @endif
                        @else
                            <flux:text>{{ __('The game has not started yet. Wait for the admin to shuffle targets.') }}</flux:text>
                        @endif
                    </div>

                    @if(!$this->registration->qr_tag_tagged_at && $this->registration->qr_tag_token)
                        <div class="flex flex-col items-center gap-3">
                            <flux:text class="font-medium">{{ __('Your QR Code') }}</flux:text>
                            <div class="p-4 bg-white rounded-xl shadow-inner border-2 border-orange-200">
                                {!! $this->registration->qrTagQrCodeSvg() !!}
                            </div>
                            <flux:text size="xs" class="text-center max-w-[200px]">{{ __('Show this to your assassin when they find you!') }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        @endif

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
                        @php
                            $isRegisteredForThisPeriod = $this->registration && $this->registration->event_period_id === $item->id;
                        @endphp
                        {{-- Period Row --}}
                        <div @class([
                        'p-1 border flex items-center justify-between rounded-lg transition-all duration-300',
                        'ring-2 ring-orange-400 bg-orange-100/50 dark:bg-orange-950/40 border-orange-400/50' => $isRegisteredForThisPeriod,
                        'border-accent-content/80' => ! $isRegisteredForThisPeriod,
                    ])>
                            <div class="flex items-center gap-2">
                                <span class="font-medium px-2 italic text-muted-foreground">{{ __('Period') }} {{ $item->number }}</span>
                                @if($isRegisteredForThisPeriod)
                                    <flux:badge :color="$this->registration->in_waitinglist ? 'yellow' : 'orange'" :icon="$this->registration->in_waitinglist ? 'clock' : 'check'" size="sm" inset="top bottom">
                                        {{ $this->registration->in_waitinglist ? __('Your Time (Waiting List)') : __('Your Time') }}
                                    </flux:badge>
                                @endif
                            </div>

                            <span class="border-l-4 border-l-orange-300 border border-orange-300 p-1 font-bold tracking-wider rounded text-sm bg-orange-300/20">
                            {{ $item->label }}
                        </span>
                        </div>
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

        @if($event->links && $event->links->count())
            <div class="mt-8">
                <h3 class="font-bold text-xl mb-4">{{ __('Links') }}</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($event->links as $link)
                        <flux:card :href="$link" class="flex items-center gap-3 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl hover:text-orange-300">
                            <flux:icon.link class="size-4 text-zinc-400" />
                            <span class="truncate text-sm font-medium">{{ $link }}</span>
                        </flux:card>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <flux:modal name="unregister-confirmation" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Unregister from event?') }}</flux:heading>
                <flux:subheading>{!! __('Are you sure you want to unregister from this event? You can always register again later if there are spots available, but you will be moved to the <span class="font-bold text-red-500">end of the queue</span>.') !!}</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" class="cursor-pointer" wire:click="unregisterUser">{{ __('Unregister') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
