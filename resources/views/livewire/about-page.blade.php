<div class="mx-auto max-w-5xl p-4 md:p-6 space-y-6">
    <flux:heading level="1" size="xl" class="text-4xl flex flex-wrap items-center justify-center gap-x-2 lg:col-span-2">
        <span>{{ __('About') }}</span>
        <x-svg.app-logo.text.light class="hidden h-[1em] translate-y-0.75 w-auto dark:block" />
        <x-svg.app-logo.text.dark class="h-[1em] w-auto translate-y-0.75 dark:hidden" />
    </flux:heading>

    <div class="space-y-4">
        <flux:heading level="2" size="xl">{{ __('What is Togethernet and what does it do?') }}</flux:heading>
        <flux:text size="lg">{{ __('Togethernet is a non-profit organisation created back in 2013, based in Stockholm Science & Innovation School. The focus of the organisation is to host social events at the school, the most famous ones being the LAN parties. Togethernet also hosts events such as movie nights, karaoke nights and QR-tag. Meetings regularly take place on Tusedays once every two weeks. During the meetings, members discuss the events: both planning them and discussing new ideas.') }}</flux:text>
        <flux:text size="lg">{{ __('Designutskottet works on our posters, patches and other designs. Planeringsgruppen\'s role is self-explanitory - they plan the events. Kommunikationsutskottet works on our announcements and other communication-related work. We also have karaokegruppen which fixes our karaoke setup for LAN and karaoke nights and Ljud Ljus & Kablar who manage our audio and electicity configurations for LAN.') }}</flux:text>
    </div>

    <flux:separator />

    <a name="collaboration"></a>
    <div class="space-y-2 -mt-1">
        <flux:heading level="2" size="xl">{{ __('Collaboration with other SSIS clubs & organizations') }}</flux:heading>
        <flux:text size="lg">{{ __('We at Togethernet aim to help other clubs with helping organise or sponsor their events. With that said, there are certain criterias we expect to be met for collaboration to take place.') }}</flux:text>
        <flux:text size="lg">{{ __('Our terms:') }}</flux:text>
        <ul>
            <li>{{ __('You have to be a club or organization located at SSIS.') }}</li>
            <li>{{ __('You cannot use AI-generated posters or text for the event we collaborate on.') }}</li>
        </ul>
        <flux:text size="sm">{{ __('Contact Togethernet styrelse for more information*') }}</flux:text>
    </div>

    <flux:separator />

    <a name="contact-info"></a>
    <div class="space-y-2 -mt-1">
        <flux:heading level="2" size="xl">{{ __('Contact Information') }}</flux:heading>
        <flux:text size="lg"><span class="font-bold">{{ __('Email') . ': ' }}</span><a href="mailto:togethernet@stockholmscience.se" class="underline hover:text-orange-300">togethernet@stockholmscience.se</a></flux:text>
        <flux:text size="lg"><span class="font-bold">{{ __('Instagram') . ': ' }}</span><a href="https://www.instagram.com/togethernet_ssis/" class="underline hover:text-orange-300">@togethernet_ssis</a></flux:text>
    </div>

    <flux:separator />

    <a name="branding"></a>
    <div class="space-y-2 -mt-1">
        <flux:heading level="2" size="xl">{{ __('Togethernet Logos & Branding') }}</flux:heading>
        <flux:heading level="3" size="l" class="mt-6">{{ __('Full-size normal') }}</flux:heading>
        <div class="flex flex-col justify-center items-center md:grid md:grid-cols-2 gap-6 mt-6 px-6">
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/full/full.svg" :alt="__('logo')">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/full/full.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                    <flux:separator vertical />
                    <a href="/logos/full/full.svg" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('SVG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
            <div>
                <div class="flex justify-center">
                    <img src="/logos/full/full_black.svg" :alt="__('logo')">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/full/full_black.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                    <flux:separator vertical />
                    <a href="/logos/full/full_black.svg" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('SVG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/full/full_white_black.png" class="w-85" :alt="__('logo')">
                </div>
                <div class="flex justify-center items-center">
                    <a href="/logos/full/full_white_black.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
        </div>

        <flux:heading level="3" size="l" class="mt-6">{{ __('Small-size normal') }}</flux:heading>
        <div class="flex flex-col justify-center items-center md:grid md:grid-cols-2 gap-6 mt-6 px-6">
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/small/small.svg" :alt="__('logo')">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/small/small.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                    <flux:separator vertical />
                    <a href="/logos/small/small.svg" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('SVG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/small/small_black.svg" :alt="__('logo')">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/small/small_black.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                    <flux:separator vertical />
                    <a href="/logos/small/small_black.svg" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('SVG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
        </div>

        <flux:heading level="3" size="l" class="mt-6">{{ __('Pixelated') }}</flux:heading>
        <div class="flex flex-col justify-center items-center md:grid md:grid-cols-3 gap-6 mt-6 px-6">
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/full/full_pixelated.png" :alt="__('logo')" class="w-85">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/full/full_pixelated.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/small/small_pixelated.png" :alt="__('logo')" class="w-12">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/small/small_pixelated.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/small/small_pixelated_black.png" :alt="__('logo')" class="w-12">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/small/small_pixelated_black.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
        </div>

        <flux:heading level="3" size="l" class="mt-6">{{ __('Old Logos') }}</flux:heading>
        <div class="flex flex-col justify-center items-center md:grid md:grid-cols-3 gap-6 mt-6 px-6">
            <div class="space-y-2">
                <div class="flex justify-center">
                    <img src="/logos/old/old_logo.png" :alt="__('logo')" class="size-20">
                </div>
                <div class="flex justify-center items-center space-x-6 md:space-x-10">
                    <a href="/logos/old/old_logo.png" download="togethernet.svg" class="flex flex-col items-center hover:text-orange-300">
                        <flux:text>{{ __('PNG') }}</flux:text>
                        <flux:icon icon="arrow-down-tray" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
