<footer {{ $attributes->merge(['class' => 'mt-auto w-full py-4 text-center -mb-6 text-sm text-muted-foreground']) }}>
    <div class="flex flex-col cursor-default">
        <span>&copy; {{ date('Y') }} Togethernet och <a href="https://www.linkedin.com/in/fedor-romanov" class="hover:underline">Fedor Romanov</a> • <a href="{{ route('legal') }}#privacy" class="hover:underline">Integritetspolicy</a> • <a href="{{ route('legal') }}#tos" class="hover:underline">Användarvillkor</a> • <a href="{{ route('faq') }}" class="hover:underline">FAQ</a></span>
    </div>
</footer>
