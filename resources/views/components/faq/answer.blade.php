<div {{ $attributes->merge(['class' => 'relative p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 dark:bg-zinc-900/50']) }}>
    <div class="leading-relaxed text-lg">
        {{ $slot }}
    </div>
</div>
