@props(['event' => new \App\Models\Event()])

<flux:modal
    :name="$event->exists ? 'edit-event' : 'create-event'"
    class="max-w-2xl w-full"
>
    <flux:heading size="lg">
        {{ $event->exists ? __('Edit Event') : __('Create Event') }}
    </flux:heading>

    <flux:subheading>
        {{ $event->exists ? __('Update the details for this event.') : __('Fill in the details to create a new event.') }}
    </flux:subheading>

    <flux:separator class="my-4" />

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

        <div class="space-y-6 max-h-[65vh] px-2 overflow-y-auto">

            {{-- Title --}}
            <flux:input
                label="{{ __('Title') }}"
                name="title"
                data-test="title-field"
                placeholder="{{ __('Enter a title for your event') }}"
                autofocus
                required
                :value="old('title', $event->title)"
            />

            {{-- Event Type --}}
            <flux:field>
                <flux:label>{{ __('Event Type') }}</flux:label>

                <div class="flex gap-x-3 mt-2">
                    @foreach(App\EventType::cases() as $event_type)
                        <button
                            type="button"
                            @click="event_type = @js($event_type->value)"
                            data-test="button-event-type-{{ $event_type->value }}"
                            class="flex-1 h-12 px-4 rounded-md font-medium transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-300/60 focus:ring-offset-2"
                            :class="event_type === @js($event_type->value)
                                ? 'bg-orange-300/90 text-accent-content font-bold shadow-sm'
                                : 'bg-transparent text-gray-600 hover:bg-orange-300/60 hover:text-gray-900'"
                        >
                            {{ $event_type->label() }}
                        </button>
                    @endforeach
                </div>

                <input type="hidden" name="event_type" :value="event_type">
                <flux:error name="event_type" />
            </flux:field>

            {{-- Description --}}
            <flux:textarea
                label="{{ __('Description') }}"
                name="description"
                data-test="description-field"
                placeholder="{{ __('Describe your event...') }}"
                required
                rows="4"
            >{{ old('description', $event->description) }}</flux:textarea>

            {{-- Number of Seats --}}
            <flux:input
                label="{{ __('Number of Seats') }}"
                name="num_of_seats"
                data-test="num_of_seats-field"
                type="number"
                min="1"
                placeholder="{{ __('Enter the number of seats available for this event') }}"
                required
                :value="old('num_of_seats', $event->num_of_seats)"
            />

            <flux:separator />

            {{-- Dates --}}
            <flux:field>
                <flux:label>{{ __('Registration Starts At') }}</flux:label>
                <flux:input
                    type="datetime-local"
                    name="display_starts_at"
                    data-test="display_starts_at"
                    :value="old('display_starts_at', $event->display_starts_at?->format('Y-m-d\TH:i'))"
                />
                <flux:error name="display_starts_at" />
            </flux:field>

            <div x-data="{ isPaid: {{ old('paid_entry', $event->paid_entry) ? 'true' : 'false' }} }"
                 class="flex flex-col md:flex-row md:items-center gap-4 w-full">

                <flux:checkbox
                    label="{{ __('Paid Entry') }}"
                    name="paid_entry"
                    class="whitespace-nowrap"
                    ::checked="isPaid"
                    x-on:change="isPaid = $el.checked"
                />

                <div x-show="isPaid" x-cloak class="w-full md:w-64">
                    <flux:input
                        name="entry_fee"
                        label="{{ __('Entry Fee') }}"
                        placeholder="{{ __('Enter amount in kr') }}"
                        value="{{ old('entry_fee', $event->entry_fee) }}"
                        type="number"
                        min="1"
                        x-bind:required="isPaid"
                    />
                    <flux:error name="entry_fee" />
                </div>
            </div>

            <div class="grid grid-col gap-y-4 md:flex md:items-center md:justify-between md:gap-x-4">
                <flux:field>
                    <flux:label>{{ __('Event Starts At') }}</flux:label>
                    <flux:input
                        type="datetime-local"
                        name="event_starts_at"
                        data-test="event_starts_at"
                        :value="old('event_starts_at', $event->event_starts_at?->format('Y-m-d\TH:i'))"
                    />
                    <flux:error name="event_starts_at" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Event Ends At') }}</flux:label>
                    <flux:input
                        type="datetime-local"
                        name="event_ends_at"
                        data-test="event_ends_at"
                        :value="old('event_ends_at', $event->event_ends_at?->format('Y-m-d\TH:i'))"
                    />
                    <flux:error name="event_ends_at" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Featured Image') }}</flux:label>

                @if($event->image_path)
                    {{-- Existing Image Preview --}}
                    <div class="mb-4" wire:key="current-image-container">
                        <img
                            src="{{ asset('storage/' . $event->image_path) }}"
                            alt="{{ __('Current featured image') }}"
                            class="w-full h-auto max-h-60 object-cover rounded-lg"
                        />

                        <flux:button
                            type="button"
                            variant="danger"
                            size="sm"
                            wire:click.prevent="clearEventImage"
                            data-test="remove-image-button"
                            class="w-full mt-2 cursor-pointer"
                        >
                            {{ __('Remove Image') }}
                        </flux:button>
                    </div>
                @endif

                <flux:input.file
                    type="file"
                    name="image"
                    id="image"
                    data-test="image-field"
                    accept="image/*"
                    x-on:change="hasImage = $event.target.files.length > 0"
                />
                <flux:error name="image" />
            </flux:field>

            {{-- Links --}}
            <flux:field>
                <flux:label>{{ __('Links') }}</flux:label>

                <div class="flex gap-x-2 items-center">
                    <flux:input
                        x-model="newLink"
                        type="url"
                        id="new-link"
                        data-test="new-link-field"
                        placeholder="https://example.com"
                        autocomplete="url"
                        class="flex-1"
                        spellcheck="false"
                    />
                    <flux:button
                        type="button"
                        icon="plus"
                        variant="ghost"
                        @click="links.push(newLink.trim()); newLink = ''"
                        x-bind:disabled="newLink.trim().length === 0"
                        data-test="add-link-button"
                        aria-label="{{ __('Add link') }}"
                    />
                </div>

                <template x-for="(link, index) in links" :key="link">
                    <div class="flex gap-x-2 items-center">
                        <flux:input
                            type="text"
                            name="links[]"
                            x-model="links[index]"
                            readonly
                            class="flex-1"
                        />
                        <flux:button
                            type="button"
                            icon="x-mark"
                            variant="ghost"
                            @click="links.splice(index, 1)"
                            data-test="remove-link-button"
                            aria-label="{{ __('Remove link') }}"
                        />
                    </div>
                </template>
            </flux:field>

        </div>

        {{-- Footer Actions --}}
        <div class="flex justify-end gap-x-3 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:modal.close>
                <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button class="cursor-pointer" type="submit" variant="primary" data-test="submit-button">
                {{ $event->exists ? __('Save Changes') : __('Create Event') }}
            </flux:button>
        </div>

    </form>

</flux:modal>
