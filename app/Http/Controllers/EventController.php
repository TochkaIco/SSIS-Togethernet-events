<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateEvent;
use App\Actions\UpdateEvent;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\GlobalLog;

class EventController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function store(EventRequest $request, CreateEvent $action)
    {
        $action->handle($request->safe()->all());

        return to_route('admin.events')
            ->with('success', __('Event created successfully'));
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
        if (! $event->canDelete()) {
            return back()->with('error', __('Cannot delete an event with participants that was created more than 30 minutes ago.'));
        }

        GlobalLog::log('Event Deleted', 'event', ['event_id' => $event->id, 'title' => $event->title]);

        $event->delete();

        return to_route('admin.events');
    }
}
