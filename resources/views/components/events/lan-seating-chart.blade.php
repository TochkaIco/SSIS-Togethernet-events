@props([
    'event' => null,
    'participants' => null,
])

@php
    $rows = $event ? $event->seatingRows() : [];

    // Seat location => registration, so booked seats can show the participant who took it.
    $registrationsBySeat = $event
        ? $event->registrations()
            ->with('user')
            ->whereNotNull('seat_location')
            ->get()
            ->keyBy('seat_location')
        : collect();
@endphp

<div class="relative w-full h-170 overflow-hidden p-8 rounded-xl">
    @if(count($rows) > 0)
        @php
            $totalRows = count($rows);
            $centerIndex = ($totalRows - 1) / 2;
            $baseRotation = 45;
        @endphp

        <div class="scale-50 md:scale-100 absolute top-2 left-12 md:left-24 pointer-events-none">
            @foreach($rows as $index => $row)
                @php
                    $angle = (($index - $centerIndex) * 22.5) + $baseRotation;
                    $rowLabel = \App\Models\Event::seatingRowLetters()[$index] ?? ($index + 1);
                @endphp

                <div class="absolute top-0 left-0 origin-top-left flex transition-transform duration-300 bg-slate-600 rounded-r"
                     style="transform: rotate({{ $angle }}deg) translate({{ 190 + ($index * 12) }}px, 0px);">
                    @foreach($row as $rowCell)
                        <div class="border border-zinc-700 m-[1px] flex flex-col">
                            @foreach($rowCell as $seat)
                                @php
                                    $seatId = $seat['seat'] ?? null;
                                    $participant = $registrationsBySeat->get($seatId)?->user;
                                    $isBooked = $participant !== null;
                                @endphp

                                <span class="seat h-8 w-8 flex items-center justify-center overflow-hidden {{ $isBooked ? 'bg-red-400 rounded-sm text-red-100' : 'bg-zinc-600 text-zinc-400' }}">
                                    <span class="block w-full truncate text-[10px] leading-none">
                                        {{ $participant ? Str::limit($participant->email, 6, end: '') : '' }}
                                    </span>
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
