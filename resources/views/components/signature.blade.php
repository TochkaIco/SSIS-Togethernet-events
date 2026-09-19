@props(['withLegal' => true])
<footer {{ $attributes->merge(['class' => 'mt-auto w-full py-4 text-center -mb-6 text-sm text-muted-foreground']) }}>
    <div class="flex flex-col cursor-default">
        <span>&copy; {{ date('Y') }} Togethernet och <a href="https://www.linkedin.com/in/sasha-romanova" class="hover:underline">Sasha Romanova</a>@if($withLegal) • <a href="{{ route('legal') }}#privacy" class="hover:underline">{{ __('Integritetspolicy') }}</a> • <a href="{{ route('legal') }}#tos" class="hover:underline">{{ __('Användarvillkor') }}</a> • <a href="{{ route('legal') }}#cookies" class="hover:underline">{{ __('Cookiepolicy') }}</a> • <a href="{{ route('faq') }}" class="hover:underline">{{ __('FAQ') }}</a>@else. Alla rättigheter förbehållna.@endif</span>
    </div>
</footer>
