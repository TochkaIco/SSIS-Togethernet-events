@props(['event' => new \App\Models\Event()])

<x-modal name="{{ $event->exists ? 'edit-event' : 'create-event' }}" title="{{ $event->exists ? __('Edit Event') : __('Create Event') }}">
    <form
        x-data="{
                    event_type: @js(old('event_type', $event->event_type?->value ?? '')),
                    newLink: '',
                    links: @js(old('links', $event->links ?? [])),
                    hasImage: false,
                }"
        action="{{ $event->exists ? route('admin.event.update', $event) : route('admin.event.store') }}"
        method="post"
        x-bind:enctype="hasImage ? 'multipart/form-data' : false"
    >
        @csrf
        @if($event->exists)
            @method('PATCH')
        @endif

        <div class="space-y-6 max-h-[70vh] overflow-y-auto px-4 py-6">
            <flux:input
                label="{{ __('Title') }}"
                name="title"
                data-test="title-field"
                placeholder="{{ __('Enter a title for your event') }}"
                autofocus
                required
                :value="old('title', $event->title)"
            />

            <div class="space-y-2">
                <flux:label>Event Type</flux:label>

                <div class="flex gap-x-3 mt-3">
                    @foreach(App\EventType::cases() as $event_type)
                        <button
                            type="button"
                            @click="event_type = @js($event_type->value)"
                            data-test="button-event-type-{{ $event_type->value }}"
                            class="flex-1 h-12 px-4 rounded-md font-medium transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-300/60 focus:ring-offset-2"
                            :class="event_type === @js($event_type->value)
                                ? 'bg-orange-400/60 text-accent-content font-bold shadow-sm hover:bg-white-700'
                                : 'bg-transparent text-gray-600 hover:bg-orange-300/60 hover:text-gray-900'"
                        >
                            {{ $event_type->label() }}
                        </button>
                    @endforeach

                    <input type="hidden" name="event_type" :value="event_type">
                </div>

                <x-form.error name="event_type" />
            </div>

            <flux:textarea
                label="{{ __('Description') }}"
                name="description"
                data-test="description-field"
                placeholder="{{ __('Describe your event...') }}"
                required
            >{{ old('description', $event->description) }}</flux:textarea>

            <div class="flex flex-col space-y-3">
                <flux:field class="w-full">
                    <flux:label for="display_starts_at">{{ __('Registration starts at') }} </flux:label>
                    <input
                        type="datetime-local"
                        name="display_starts_at"
                        data-test="display_starts_at"
                        value="{{ old('display_starts_at', $event->display_starts_at?->format('Y-m-d\TH:i')) }}"
                    >
                    <x-form.error name="display_starts_at" />
                </flux:field>

                <flux:separator />

                <div class="flex items-center justify-between">
                    <flux:field class="w-min">
                        <flux:label for="event_starts_at">{{ __('Event starts at') }} </flux:label>
                        <input
                            type="datetime-local"
                            name="event_starts_at"
                            data-test="event_starts_at"
                            value="{{ old('event_starts_at', $event->event_starts_at?->format('Y-m-d\TH:i')) }}"
                        >
                        <x-form.error name="event_starts_at" />
                    </flux:field>

                    <flux:field class="w-min">
                        <flux:label for="event_ends_at">{{ __('Event ends at') }} </flux:label>
                        <input
                            type="datetime-local"
                            name="event_ends_at"
                            data-test="event_ends_at"
                            value="{{ old('event_ends_at', $event->event_ends_at?->format('Y-m-d\TH:i')) }}"
                        >
                        <x-form.error name="event_ends_at" />
                    </flux:field>
                </div>
            </div>

            <div class="flex flex-col gap-y-3">
                <flux:label for="image">{{ __('Featured Image') }}</flux:label>

                @if($event->image_path)
                    <div class="space-y-2">
                        <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}"
                             class="w-full h-auto max-h-60 object-cover mb-2 rounded-lg">
                        <flux:button variant="danger" class="h-10 w-full" form="delete-image-form">{{ __('Remove Image') }}</flux:button>
                    </div>
                @endif

                <flux:input.file
                    type="file"
                    name="image"
                    id="image"
                    data-test="image-field"
                    accept="image/*"
                    @change="hasImage = $event.target.files.length > 0"
                />
                <x-form.error name="image" />
            </div>

            <div>
                <fieldset class="space-y-3">
                    <flux:label>{{ __('Links') }}</flux:label>

                    <div class="flex gap-x-2 items-center">
                        <flux:input
                            x-model="newLink"
                            type="url"
                            id="new-link"
                            data-test="new-link-field"
                            placeholder="https://example.com"
                            autocomplete="url"
                            class="input flex-1"
                            spellcheck="false"
                        />

                        <button
                            type="button"
                            @click="links.push(newLink.trim()); newLink='';"
                            :disabled="newLink.trim().length === 0"
                            class="text-gray-400 cursor-pointer"
                            data-test="add-link-button"
                            aria-label="Add link button"
                        >
                            <x-icons.close class="rotate-45" />
                        </button>
                    </div>

                    <template x-for="(link, index) in links" :key="link">
                        <flux:button-or-div class="flex gap-x-2 items-center justify-between">
                            <input
                                type="text"
                                name="links[]"
                                x-model="link"
                                class="input"
                                readonly
                            >

                            <button
                                type="button"
                                @click="links.splice(index, 1)"
                                class="cursor-pointer text-gray-400"
                                data-test="remove-link-button"
                                aria-label="Remove link button"
                            >
                                <x-icons.close />
                            </button>
                        </flux:button-or-div>
                    </template>
                </fieldset>
            </div>

            <div class="flex justify-end gap-x-5">
                <flux:button type="button" class="cursor-pointer" variant="ghost" @click="$dispatch('close-modal')">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary" class="cursor-pointer" data-test="submit-button">{{ $event->exists ? __('Save') : __('Create') }}</flux:button>
            </div>
        </div>
    </form>

    @if($event->image_path)
        <form action="{{ route('admin.event.image.destroy', $event) }}" id="delete-image-form" method="post">
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-modal>
