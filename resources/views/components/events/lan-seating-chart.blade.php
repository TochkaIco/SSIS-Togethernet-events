@props([
    'event' => null,
    'participants' => null,
    'isDisabled' => false,
    'showParticipantEmails' => true,
    'ownRegistrationId' => null,
    'selectedSeatId' => null,
])

@php
    $rows = $event ? $event->seatingRows() : [];

    $registrationsBySeat = $event
        ? $event->registrations()
            ->with('user')
            ->whereNotNull('seat_location')
            ->get()
            ->keyBy('seat_location')
        : collect();
@endphp

<div class="relative w-full h-85 md:h-160 overflow-hidden p-8 rounded-xl">
    @if(count($rows) > 0)
        @php
            $totalRows = count($rows);
            $centerIndex = ($totalRows - 1) / 2;
            $baseRotation = 45;
        @endphp

        <div class="scale-50 md:scale-100 absolute top-2 left-12 md:left-24 {{ $isDisabled ? 'pointer-events-none' : '' }}">
            @foreach($rows as $index => $row)
                @php
                    $angle = (($index - $centerIndex) * 22.5) + $baseRotation;
                    $rowLabel = \App\Models\Event::seatingRowLetters()[$index] ?? ($index + 1);
                @endphp

                <div class="absolute top-0 left-0 origin-top-left flex transition-transform duration-300 bg-slate-600 rounded-r"
                     style="transform: rotate({{ $angle }}deg) translate({{ 190 + ($index * 12) }}px, 0px);">
                    @foreach($row as $rowCell)
                        <div class="border border-zinc-700 m-[0.5px] flex flex-col">
                            @foreach($rowCell as $seat)
                                @php
                                    $seatId = $seat['seat'] ?? null;
                                    $registration = $registrationsBySeat->get($seatId);
                                    $participant = $registration?->user;
                                    $isBooked = $registration !== null;
                                    $isOwnSeat = $isBooked && $registration->id === $ownRegistrationId;
                                    $showsEmail = $participant !== null && ($showParticipantEmails || $registration->id === $ownRegistrationId);
                                @endphp

                                <span @class([
                                    'seat h-8 w-8 flex items-center justify-center overflow-hidden',
                                    'bg-emerald-500 hover:bg-emerald-300 rounded-sm text-white' => $seatId === $selectedSeatId,
                                    'bg-amber-400 hover:bg-amber-200 rounded-sm text-amber-900' => $seatId !== $selectedSeatId && $isOwnSeat,
                                    'bg-red-400 hover:bg-red-200 rounded-sm text-red-100' => $seatId !== $selectedSeatId && $isBooked && ! $isOwnSeat,
                                    'bg-zinc-600 hover:bg-zinc-400 text-zinc-400' => $seatId !== $selectedSeatId && ! $isBooked,
                                ])>
                                    @if($seatId !== null)
                                        <button type="button" wire:click="selectSeat('{{ $seatId }}')" class="block w-full h-full truncate text-[10px] leading-none">
                                            {{ $showsEmail ? Str::limit($participant->email, 6, end: '') : '' }}
                                        </button>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @endforeach
                    <flux:label class="flex -rotate-90 ml-2 mr-2">{{ $rowLabel }}</flux:label>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-slate-400 text-sm">{{ __('No seating layout found.') }}</div>
    @endif
</div>
