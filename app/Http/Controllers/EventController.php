<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateEvent;
use App\Actions\UpdateEvent;
use App\Http\Requests\EventRequest;
use App\Models\Event;

class EventController extends Controller
{

    public function admin_index()
    {
        $events = Event::all();

        return view('livewire.admin.events.index', [
            'events' => $events,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function store(EventRequest $request, CreateEvent $action)
    {
        $action->handle($request->safe()->all());

        return to_route('admin.events')
            ->with('success', __('Event created successfully'));
    }

    public function admin_show(Event $event)
    {
        return view('livewire.admin.events.show', [
            'event' => $event,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws \Throwable
     */
    public function update(EventRequest $request, Event $event, UpdateEvent $action)
    {
        $action->handle($request->safe()->all(), $event);

        return back()->with('success', __('Event updated!'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return to_route('admin.events');
    }
}
