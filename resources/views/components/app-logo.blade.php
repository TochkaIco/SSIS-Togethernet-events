@props([
    'sidebar' => false,
])

@php
    $href = $attributes->get('href') ?? null;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->except(['sidebar', 'href'])->merge(['class' => 'flex items-center overflow-hidden']) }}>
        <x-svg.app-logo.text.dark class="block dark:hidden shrink-0 w-full" />
        <x-svg.app-logo.text.light class="hidden dark:block shrink-0 w-full" />
    </a>
@else
    <div {{ $attributes->except(['sidebar'])->merge(['class' => 'flex items-center overflow-hidden']) }}>
        <x-svg.app-logo.text.dark class="block dark:hidden shrink-0 w-full" />
        <x-svg.app-logo.text.light class="hidden dark:block shrink-0 w-full" />
    </div>
@endif
