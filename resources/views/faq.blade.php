<x-layouts::app title="FAQ">
    <div class="mx-auto grid max-w-5xl justify-center gap-12 p-6 grid-cols-1">
        <div class="flex flex-wrap items-center justify-center gap-x-2 text-4xl lg:col-span-2">
            <span>Välkommen till</span>
            <x-svg.app-logo.text.light class="hidden h-[1em] translate-y-0.75 w-auto dark:block" />
            <x-svg.app-logo.text.dark class="h-[1em] w-auto translate-y-0.75 dark:hidden" />
            <span>FAQ</span>
        </div>
        <div class="flex flex-col gap-6">
            <x-faq.question>
                Hur går jag med i Togethernets Discord-server?
            </x-faq.question>
            <x-faq.answer>
                Klicka här för att gå med i vår Discord! <a href="https://discord.gg/WDYR8DP9gc" class="hover:underline hover:text-orange-300 cursor-pointer text-muted-foreground">https://discord.gg/WDYR8DP9gc</a>
            </x-faq.answer>

            <x-faq.question>
                Webbplatsen fungerar inte som den ska, vad gör jag?
            </x-faq.question>
            <x-faq.answer>
                Om du upptäcker några fel – kontakta gärna vår nuvarande maintainer på <a class="hover:underline hover:text-orange-300 cursor-pointer text-muted-foreground" href="mailto:{{ config('app.dev_info.maintainer_email') }}">{{ config('app.dev_info.maintainer_email') }}</a>
            </x-faq.answer>

            <flux:separator />

            <h1 class="text-center">
                Regler
            </h1>
            <x-faq.answer>
                <b>1 §</b> Alkohol och droger är strikt förbjudna under Togethernet. Oavsett om de förekommer <i>innan eller
                    under</i>. <b>Om vi märker av beteende som bryter mot detta, kommer Togethernet be dig lämna lokalen
                    omedelbart</b>.
            </x-faq.answer>
            <x-faq.answer>
                <b>2 §</b> Om det uppstår bråk eller ovänligheter av bl.a. rasistisk, sexistisk, antisemitisk eller annan
                kränkande natur <b>kommer Togethernet att be dig lämna lokalen omedelbart.</b> På Togethernet har man kul,
                det är ingen plats för hat.
            </x-faq.answer>
            <x-faq.answer>
                <b>3 §</b> Eftersom Togethernet befinner sig i SSIS lokaler följer vi således också skolans värdegrund. Alla etikettsregler som gäller i
                skolan, gäller också under Togethernet.
            </x-faq.answer>
            <x-faq.answer>
                <b>4 §</b> Togethernet liksom SSIS <b>är en nötfri zon</b>. Personer med akut nötallergi kan befinna sig på
                eventet och med respekt för dem, ber vi er att inte ta med er något med nötter i. Om du bryter detta kommer
                vi beslagta produkterna som innehåller nötter.
            </x-faq.answer>
            <x-faq.answer>
                <b>5 §</b> Vi ber alla som deltar under Togethernets event att behandla varandra med respekt. Dina saker får
                således inte störa dina grannar, håll dig inom din bordsplats och <b>bete dig</b>.
            </x-faq.answer>
            <x-faq.answer>
                <b>6 §</b> Det är inte tillåtet att släppa in <b>icke SSIS:are</b> under något av våra event. <b>Om du bryter mot detta
                    blir både du och den insläppta ombedda att lämna lokalen omedelbart.</b>
            </x-faq.answer>
            <x-faq.answer>
                <b>7 §</b> Deltagare på Togethernets får <b>inte under några omständigheter förstöra eller försöka sabotera</b>
                något av skolan eller andra deltagares materiel. Om någon blir ertappad med att göra det kommer Togethernet
                be dig att lämna lokalen.
            </x-faq.answer>
            <x-faq.answer>
                <b>8 §</b> <b>Inga olagliga aktiviteter</b> är tillåtna via <b>nätverket</b> och om det förekommer blir den
                ansvariga anmäld av skolan.
            </x-faq.answer>
            <x-faq.answer>
                <b>9 §</b> Det är inte tillåtet att koppla in <b>elektronik i andra kontakter</b> än givna vid placeringen
                utan tillstånd av Togethernet.
            </x-faq.answer>
            <x-faq.answer>
                <b>10 §</b> Det är inte tillåtet att vara i <b>andra delen av skolan</b> vilket löper från början av
                lärarrummet och framåt utan tillåtelse.
            </x-faq.answer>
            <x-faq.answer>
                <b>11 §</b> Togethernet och lärare administrerar eventet och deras beslut är <b>slutgiltiga</b>.
            </x-faq.answer>
            <x-faq.answer>
                <b>12 §</b> Regelbrott emot <b>§1, §2, §7, §8 och §11</b>
                kommer att rapporteras till rektorn.
            </x-faq.answer>
            <x-faq.answer>
                <b>Togethernet reserverar sig att ändra och eventuellt bryta mot reglerna ovan i mån att det är rimligt.</b>
            </x-faq.answer>
        </div>
    </div>
</x-layouts::app>
