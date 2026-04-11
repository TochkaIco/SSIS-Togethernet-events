@props(['userClass' => 'Unknown'])

@if(str($userClass)->contains('TE'.now()->subMonths(6)->format('y')))
    <flux:badge size="sm" color="blue" class="whitespace-nowrap">
        {{ $userClass }}
    </flux:badge>
@elseif(str($userClass)->contains('TE'.now()->subMonths(6)->subYear()->format('y')))
    <flux:badge size="sm" color="green" class="whitespace-nowrap">
        {{ $userClass }}
    </flux:badge>
@elseif(str($userClass)->contains('TE'.now()->subMonths(6)->subYears(2)->format('y')))
    <flux:badge size="sm" color="yellow" class="whitespace-nowrap">
        {{ $userClass }}
    </flux:badge>
@else
    <flux:badge size="sm" color="red" class="whitespace-nowrap">
        {{ $userClass }}
    </flux:badge>
@endif
