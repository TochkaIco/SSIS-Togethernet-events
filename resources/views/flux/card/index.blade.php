@props([
    'size' => 'default',
    'href' => null,
    'image' => null, // Added image prop for convenience
])

@php
    $as = $href ? 'a' : 'div';

    $classes = Flux::classes()
        ->add('bg-white dark:bg-white/10')
        ->add('border border-zinc-200 dark:border-white/10')
        ->add('rounded-xl overflow-hidden flex flex-col') // Added overflow-hidden & flex
        ->add($href ? 'cursor-pointer transition hover:bg-zinc-50 dark:hover:bg-white/[0.05]' : '');

    // Set the text padding based on size
    $padding = match ($size) {
        'sm' => 'p-4',
        default => 'p-6',
    };
@endphp

<{{ $as }} {{ $href ? 'href=' . $href : '' }} {{ $attributes->class($classes) }}>
{{-- 1. The Image Section (No margins needed!) --}}
@if($image)
    <div class="w-full h-48 shrink-0">
        {{ $image }}
    </div>
@endif

{{-- 2. The Content Section (Handles the padding) --}}
<div class="{{ $padding }} flex-1 flex flex-col">
    {{ $slot }}
</div>
</{{ $as }}>
