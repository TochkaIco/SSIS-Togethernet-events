<div class="py-8 max-w-md md:max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('events') }}" icon="layout-grid">{{ __('Events') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('event.show', $event) }}">{{ $event->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-1 md:space-x-3">
                <h1 class="font-bold text-2xl sm:text-3xl wrap-break-word min-w-0 flex-1">{{ $event->title }}</h1>
                @if($event->isFinished())
                    <flux:badge size="sm" icon="flag" class="cursor-default bg-red-500/40! border-zinc-500/20 px-3 py-1 justify-center shrink-0">
                        {{ __('Event Finished') }}
                    </flux:badge>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full sm:w-auto">
                @if(auth()->user())
                    @if(! $this->registration)
                        @if($event->canRegister())
                            @if($event->allowsUser(auth()->user()))
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
                                <flux:button wire:click="registerUser({{ $event->id }})" variant="primary" class="cursor-pointer transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl w-full sm:w-auto">{{ __('Register') }}</flux:button>
                            @else
                                <flux:button disabled variant="ghost" class="cursor-not-allowed text-red-500! border-red-500/20 bg-red-500/5! w-full sm:w-auto">{{ __('Domain Restricted') }}</flux:button>
                            @endif
                        @endif
                    @else
                        <div class="flex flex-col sm:items-end gap-2 w-full sm:w-auto">
                            <div class="flex flex-col items-stretch sm:items-end gap-2 w-full">
                                @if($event->one_hour_periods && $this->registration->event_period_id)
                                    @php
                                        $periodLabel = $this->registration->eventPeriod?->label;
                                    @endphp
                                    <flux:badge color="orange" icon="clock" class="justify-center sm:justify-start">{{ __('Period') }}: {{ $periodLabel }}</flux:badge>
                                @endif

                                {{-- Status Badge --}}
                                <div class="relative w-full flex sm:justify-end">
                                    @if($this->registration->in_waitinglist)
                                        <div class="absolute -inset-1 bg-yellow-500/20 blur-lg rounded-full"></div>
                                        <flux:badge size="sm" icon="clock" class="cursor-default relative bg-yellow-500/10! text-yellow-400! border-yellow-500/20 px-3 py-1 w-full sm:w-auto justify-center">
                                            {{ __('On Waiting List') }}
                                        </flux:badge>
                                    @else
                                        <div class="absolute -inset-1 bg-emerald-500/20 blur-lg rounded-full"></div>
                                        <flux:badge size="sm" icon="check" class="cursor-default relative bg-emerald-500/10! text-emerald-400! border-emerald-500/20 px-3 py-1 w-full sm:w-auto justify-center">
                                            {{ __('Registered as Participant') }}
                                        </flux:badge>
                                    @endif
                                </div>

                                @if($event->canUnregister())
                                    <flux:modal.trigger name="unregister-confirmation" class="flex justify-stretch sm:justify-end w-full">
                                        <flux:button
                                            variant="ghost"
                                            size="xs"
                                            icon="x-mark"
                                            wire:click="confirmUnregister({{ $event->id }})"
                                            class="cursor-pointer bg-white/5! text-zinc-300! border border-white/10 hover:bg-red-500/20! hover:!text-red-400 hover:border-red-500/30 transition-all w-full sm:w-auto"
                                        >
                                            {{ __('Unregister') }}
                                        </flux:button>
                                    </flux:modal.trigger>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    @if($event->canRegister())
                        <flux:button href="{{ route('auth.login') }}" icon="user-plus" variant="primary" class="cursor-pointer transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl w-full sm:w-auto">{{ __('Login to register') }}</flux:button>
                    @endif
                @endif
            </div>
        </div>
    </div>
    <div class="mt-6">
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
            <div class="rounded-lg overflow-hidden mb-6 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
            </div>
        @else
            <div class="rounded-lg overflow-hidden mb-6 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
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

        @if($event->description)
            <flux:card class="mt-6 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    {!! $event->formattedDescription !!}
                </div>
            </flux:card>
        @endif

        @if($event->event_type === \App\EventType::QR_TAG)
            <div class="mt-6 space-y-6">
                {{-- User Game Info (only if registered) --}}
                @if($this->registration && !$this->registration->in_waitinglist)
                    <flux:card class="bg-orange-50 dark:bg-orange-950/20 border-orange-200 dark:border-orange-900 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                        <div class="flex flex-col lg:flex-row gap-6 md:gap-8 items-center lg:items-start">
                            <div class="flex-1 space-y-4 w-full">
                                <div class="flex items-start gap-2">
                                    <flux:icon.sparkles class="text-orange-500" />
                                    <flux:heading size="lg">{{ __('Your Game Status') }}</flux:heading>
                                </div>

                                @if($event->isFinished())
                                    <div class="p-4 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200 rounded-lg border border-orange-200 dark:border-orange-800">
                                        <p class="font-bold text-lg">{{ __('The event has finished!') }}</p>
                                        <p class="text-sm">
                                            {{ __('You finished on spot :rank', ['rank' => $this->registration->qrTagRank()]) }}
                                        </p>
                                    </div>
                                @elseif($this->registration->qr_tag_tagged_at)
                                    <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-lg border border-red-200 dark:border-red-800">
                                        <p class="font-bold">{{ __('You have been tagged!') }}</p>
                                        <p class="text-sm">
                                            {{ __('Tagged by :name at :time', [
                                                'name' => $this->registration->taggedBy->name,
                                                'time' => $this->registration->qr_tag_tagged_at->format('H:i')
                                            ]) }}
                                        </p>
                                    </div>
                                @elseif($this->registration->is_disabled)
                                    <div class="p-4 bg-zinc-100 dark:bg-zinc-900/30 text-zinc-800 dark:text-zinc-200 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                        <p class="font-bold">{{ __('You are currently disabled.') }}</p>
                                        <span class="flex-col md:flex space-x-1">
                                            <p class="text-sm">{{ __('An admin has disabled you for this event. You cannot tag or be tagged.') }}</p>
                                            <a href="{{ route('faq') . '#qrtag-rules-disabled-player' }}" class="text-sm underline hover:text-orange-300">{{ __('Learn more...') }}</a>
                                        </span>
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
                                            <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden min-w-0">
                                                <flux:avatar :initials="$this->registration->targetUser->initials()" size="sm" class="shrink-0 {{ $this->registration->qrTagStreak() ? 'ring-2 ring-orange-500/70' : '' }}" />
                                                <span class="flex font-bold text-lg truncate">
                                                    {{ Str::limit($this->registration->targetUser->name, 18) }}
                                                    @if($streak = $this->registration->qrTagStreak())
                                                        <span class="flex items-center gap-x-1 ml-2 bg-orange-600 text-white rounded-full px-2 py-0.5 text-xs font-medium"><flux:icon icon="fire" /> {{ $streak }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <flux:text size="sm" class="italic">{{ __('Find them and scan their QR-code to tag them.') }}</flux:text>

                                            <flux:text size="sm">{{ __('Or enter their qr-tag token directly if cannot scan the qr-code.') }}</flux:text>
                                            <flux:button size="sm" wire:click="openQrTagTokenEntryModal">{{ __('Enter qr-tag token') }}</flux:button>
                                        </div>
                                    @endif
                                @else
                                    <flux:text>{{ __('The game has not started yet. Wait for the admin to shuffle targets.') }}</flux:text>
                                @endif
                            </div>

                            @if($this->registration && !$this->registration->qr_tag_tagged_at && !$this->registration->is_disabled && $this->registration->qr_tag_token && !$event->isFinished())
                                <div class="flex flex-col items-center gap-3 shrink-0 max-w-full">
                                    <flux:text class="font-medium">{{ __('Your QR Code') }}</flux:text>
                                    <div class="p-4 bg-white rounded-xl shadow-inner border-2 border-orange-400 max-w-full overflow-hidden">
                                        <div class="[&>svg]:max-w-full [&>svg]:h-auto">
                                            {!! $this->registration->qrTagQrCodeSvg() !!}
                                        </div>
                                    </div>

                                    <flux:text size="sm" class="font-medium mt-4">{{ __('Your QR-Tag Token') }}</flux:text>
                                    <flux:button size="sm" wire:click="openQrTagTokenModal" class="w-full">{{ __('View') }}</flux:button>
                                </div>
                            @endif
                        </div>
                    </flux:card>
                @endif

                {{-- Player Stats Card --}}
                <div class="grid grid-cols-2 gap-4">
                    <flux:card class="flex flex-col items-center justify-center md:py-8 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                        <flux:text size="lg" class="uppercase tracking-widest text-muted-foreground">{{ __('Active Players') }}</flux:text>
                        <flux:text size="xl" class="font-bold text-orange-500 mt-2">{{ $event->qrTagActiveParticipantsCount() }}</flux:text>
                    </flux:card>
                    <flux:card class="flex flex-col items-center justify-center md:py-8 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                        <flux:text size="lg" class="uppercase tracking-widest text-muted-foreground">{{ __('Total Players') }}</flux:text>
                        <flux:text size="xl" class="font-bold mt-2">{{ $event->participants()->count() }}</flux:text>
                    </flux:card>
                </div>

                {{-- Leaderboard --}}
                @auth
                    <flux:card class="transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                        <div class="flex items-center gap-2 mb-6">
                            <flux:icon.trophy class="text-orange-500" />
                            <flux:heading size="lg">{{ __('Top 5 Players') }}</flux:heading>
                        </div>

                        <div class="space-y-4">
                            @forelse($event->qrTagLeaderboard() as $index => $leader)
                                <div class="flex items-center gap-4 p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 shadow-sm">
                                    <div @class([
                                    'size-8 flex items-center justify-center rounded-full font-bold text-white shrink-0',
                                    'bg-yellow-500' => $index === 0,
                                    'bg-zinc-400' => $index === 1,
                                    'bg-orange-700' => $index === 2,
                                    'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' => $index > 2,
                                ])>
                                        {{ $index + 1 }}
                                    </div>

                                    <flux:avatar src="{{ $leader->user->profile_picture }}" :initials="$leader->user->initials()" size="sm" class="shrink-0 {{ $leader->qrTagStreak() ? 'ring-2 ring-orange-400/70' : '' }}" />

                                    <div class="flex-1 min-w-0">
                                        <div class="flex text-xs md:text-lg font-bold truncate">
                                            {{ Str::limit($leader->user->name, 15) }}
                                            @if($streak = $leader->qrTagStreak())
                                                <span class="flex items-center gap-x-1 ml-2 bg-orange-600 text-white rounded-full px-2 py-0.5 text-xs font-medium"><flux:icon icon="fire" /> {{ $streak }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end shrink-0">
                                        <div class="text-lg font-bold text-orange-500">{{ $leader->qr_tag_count }}</div>
                                        <div class="text-[10px] uppercase tracking-tighter text-muted-foreground">{{ __('Tags') }}</div>
                                    </div>
                                </div>
                            @empty
                                <flux:text class="italic">{{ __('No tags yet.') }}</flux:text>
                            @endforelse
                        </div>
                    </flux:card>
                @endauth

                {{-- Game Logs --}}
                @auth
                    <flux:card class="p-0 sm:p-6 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                        <flux:heading size="lg" class="m-4 sm:m-0 mb-4 sm:mb-6">{{ __('Game Log') }}</flux:heading>
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($event->qrTagLogs()->with(['user', 'targetUser'])->take(60)->get() as $log)
                                <div class="flex items-start gap-3 text-sm p-4 sm:px-0 first:pt-0 last:pb-0">
                                    <div class="mt-1 shrink-0">
                                        @switch($log->type)
                                            @case('tagged')
                                                <flux:icon.user-minus class="size-4 text-red-500" />
                                                @break
                                            @case('respawn')
                                            @case('respawn_all')
                                                <flux:icon.sparkles class="size-4 text-purple-500" />
                                                @break
                                            @case('started')
                                                <flux:icon.play class="size-4 text-green-500" />
                                                @break
                                            @case('reset')
                                                <flux:icon.arrow-path class="size-4 text-zinc-500" />
                                                @break
                                            @case('reshuffled')
                                                <flux:icon.arrow-path class="size-4 text-orange-500" />
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="leading-relaxed">
                                            @if($log->type === 'tagged')
                                                <span class="font-bold">{{ $log->user->name }}</span> {{ __('tagged') }} <span class="font-bold">{{ $log->targetUser->name }}</span>
                                            @elseif($log->type === 'respawn')
                                                <span class="font-bold">{{ $log->user->name }}</span> {{ __('was respawned by an admin.') }}
                                            @elseif($log->type === 'respawn_all')
                                                {{ __('All caught players were respawned!') }}
                                            @elseif($log->type === 'started')
                                                {{ __('The game has started! Targets have been shuffled.') }}
                                            @elseif($log->type === 'reset')
                                                {{ __('The game was reset by an admin.') }}
                                            @elseif($log->type === 'reshuffled')
                                                {{ __('A loop was detected and targets have been re-shuffled.') }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-muted-foreground mt-1">
                                            {{ $log->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 sm:px-0">
                                    <flux:text class="italic">{{ __('No logs yet.') }}</flux:text>
                                </div>
                            @endforelse
                        </div>
                    </flux:card>
                @endauth
            </div>
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
                        'p-1 border flex items-center justify-between rounded-lg transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl',
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
    @if($this->registration())
        <flux:modal name="qr-tag-token-modal" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Your Qr-Tag Token') }}</flux:heading>
                    <flux:subheading>{{ __('Show this to your hunter if they cannot scan qr-codes.') }}</flux:subheading>
                </div>

                <flux:textarea disabled>{!! $this->registration()->qr_tag_token !!}</flux:textarea>

                <div class="flex">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="primary" class="cursor-pointer">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endif
    <flux:modal name="qr-tag-token-entry-modal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div class="max-w-64 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{ __('Qr-Tag Token') }}</flux:heading>
                <flux:text class="text-center">{{ __('Please enter your target\'s qr-tag token.') }}</flux:text>
            </div>
            <div>
                <div x-data="{
                    code: @entangle('qrTagGivenToken').live,
                    maxLength: 32,
                    visibleLength: 6,
                    get visibleChars() {
                        let actualChars = (this.code || '').split('');
                        if (actualChars.length <= this.visibleLength) {
                            while (actualChars.length < this.visibleLength) {
                                actualChars.push('');
                            }
                            return actualChars;
                        }
                        return actualChars.slice(-this.visibleLength);
                    },
                    get remaining() {
                        return this.maxLength - (this.code ? this.code.length : 0);
                    }
                }" class="relative max-w-xs mx-auto group">

                    <label class="sr-only">OTP Code</label>
                    <input
                        type="text"
                        x-model="code"
                        :maxlength="maxLength"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-text z-10 focus:outline-none"
                        autofocus
                    />

                    <div class="flex justify-center gap-2 pointer-events-none">
                        <template x-for="(char, index) in visibleChars" :key="index">
                            <div
                                class="w-11 h-14 flex items-center justify-center border rounded-xl text-lg font-medium transition-all duration-150 shadow-sm
                                  group-focus-within:border-zinc-400 dark:group-focus-within:border-zinc-500"
                                :class="[
                                    char ? 'border-zinc-800 dark:border-white dark:text-white ring-1 ring-zinc-800 dark:ring-white' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800',
                                    @error('tokenEntry') 'border-red-500 dark:border-red-500 ring-red-500 dark:ring-red-500' @enderror
                                ]"
                            >
                                <span
                                    x-text="char"
                                    x-show="char"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-x-2"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                ></span>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3 text-center h-5">
                        <span
                            x-show="remaining > 0"
                            x-text="remaining + ' {{ __('characters remaining') }}'"
                            class="text-xs font-medium text-zinc-400 dark:text-zinc-500 tracking-wide uppercase transition-opacity duration-200"
                            style="display: none;"
                        ></span>
                    </div>
                </div>

                @error('tokenEntry')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400 text-center">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-4">
                <flux:button variant="primary" wire:click="tagQrTagTargetWithToken" :disabled="strlen($qrTagGivenToken) < 32" class="w-full">{{ __('Continue') }}</flux:button>
                <flux:modal.close>
                    <flux:button class="w-full">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
