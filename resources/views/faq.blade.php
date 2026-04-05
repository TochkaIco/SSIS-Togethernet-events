<x-layouts::app :title="__('FAQ')">
    <div class="mx-auto grid max-w-5xl justify-center gap-12 p-6 grid-cols-1">
        <div class="flex flex-wrap items-center justify-center gap-x-2 text-4xl lg:col-span-2">
            <span>{{ __('Welcome to') }}</span>
            <x-svg.app-logo.text.light class="hidden h-[1em] translate-y-0.75 w-auto dark:block" />
            <x-svg.app-logo.text.dark class="h-[1em] w-auto translate-y-0.75 dark:hidden" />
            <span>{{ __('FAQ') }}</span>
        </div>
        <div class="flex flex-col gap-6">
            <x-faq.question>
                {{ __('How do I join Togethernet\'s Discord server?') }}
            </x-faq.question>
            <x-faq.answer>
                {!! __('Click here to join our Discord! <a href="https://discord.gg/WDYR8DP9gc" class="hover:underline hover:text-orange-300 cursor-pointer text-muted-foreground">https://discord.gg/WDYR8DP9gc</a>') !!}
            </x-faq.answer>

            <x-faq.question>
                {{ __('The website is not working correctly, what do I do?') }}
            </x-faq.question>
            <x-faq.answer>
                {!! __('If you discover any errors – please contact our current maintainer at :email', [
                    'email' => '<a class="hover:underline hover:text-orange-300 cursor-pointer text-muted-foreground" href="mailto:' . config('app.dev_info.maintainer_email') . '">' . config('app.dev_info.maintainer_email') . '</a>'
                ]) !!}
            </x-faq.answer>

            <flux:separator />

            <h1 class="text-center">
                {{ __('Rules') }}
            </h1>
            <x-faq.answer>
                {!! __('<b>1 §</b> Alcohol and drugs are strictly prohibited during Togethernet. Whether they occur <i>before or during</i>. <b>If we notice behavior that violates this, Togethernet will ask you to leave the premises immediately</b>.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>2 §</b> If any fights or unpleasantness of e.g. racist, sexist, antisemitic or other offensive nature occur, <b>Togethernet will ask you to leave the premises immediately.</b> At Togethernet we have fun, it is no place for hate.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>3 §</b> Since Togethernet is located in SSIS premises, we also follow the school\'s core values. All etiquette rules that apply in school also apply during Togethernet.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>4 §</b> Togethernet as well as SSIS <b>is a nut-free zone</b>. People with acute nut allergies may be present at the event and with respect for them, we ask you not to bring anything with nuts in it. If you break this, we will confiscate the products containing nuts.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>5 §</b> We ask everyone participating in Togethernet\'s events to treat each other with respect. Your belongings must therefore not disturb your neighbors, stay within your table space and <b>behave yourself</b>.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>6 §</b> It is not allowed to let in <b>non-SSIS students</b> during any of our events. <b>If you violate this, both you and the person let in will be asked to leave the premises immediately.</b>') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>7 §</b> Participants at Togethernet may <b>under no circumstances destroy or attempt to sabotage</b> any of the school\'s or other participants\' material. If anyone is caught doing so, Togethernet will ask you to leave the premises.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>8 §</b> <b>No illegal activities</b> are allowed via the <b>network</b> and if it occurs, the responsible person will be reported by the school.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>9 §</b> It is not allowed to plug <b>electronics into other sockets</b> than those given at the placement without permission from Togethernet.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>10 §</b> It is not allowed to be in the <b>other part of the school</b> which runs from the beginning of the staff room and forward without permission.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>11 §</b> Togethernet and teachers administer the event and their decisions are <b>final</b>.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>12 §</b> Violations of <b>§1, §2, §7, §8 and §11</b> will be reported to the principal.') !!}
            </x-faq.answer>
            <x-faq.answer>
                {!! __('<b>Togethernet reserves the right to change and possibly violate the rules above to the extent that it is reasonable.</b>') !!}
            </x-faq.answer>
        </div>
    </div>
</x-layouts::app>
