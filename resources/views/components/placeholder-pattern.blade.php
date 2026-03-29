@props([
    'id' => uniqid(),
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden']) }}>
    <svg class="absolute inset-0 size-full" fill="none" aria-hidden="true">
        <defs>
            <pattern id="pattern-{{ $id }}" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                <path d="M-1 5L5 -1M3 9L8.5 3.5" stroke-width="0.5"></path>
            </pattern>
        </defs>
        <rect stroke="none" fill="url(#pattern-{{ $id }})" width="100%" height="100%"></rect>
    </svg>

    <div class="relative z-10 flex items-center justify-center h-full w-full">
        {{ $slot }}
    </div>
</div>
