<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\EventType;
use App\Models\EventUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class QrTagController extends Controller
{
    public function scan(string $token): RedirectResponse
    {
        $victimRegistration = EventUser::where('qr_tag_token', $token)
            ->with(['event', 'user'])
            ->firstOrFail();

        if ($victimRegistration->event->event_type !== EventType::QR_TAG) {
            return redirect()->route('homepage')->with('error', __('Invalid event type.'));
        }

        if ($victimRegistration->qr_tag_tagged_at) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('This user has already been tagged.'));
        }

        $assassinRegistration = EventUser::where('event_id', $victimRegistration->event_id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $assassinRegistration) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('You are not registered for this event.'));
        }

        if ($assassinRegistration->qr_tag_tagged_at) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('You are already out of the game.'));
        }

        if ($assassinRegistration->qr_tag_target_user_id !== $victimRegistration->user_id) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('This is not your target.'));
        }

        // Successful tag
        $victimRegistration->update([
            'qr_tag_tagged_at' => now(),
            'qr_tag_tagged_by_user_id' => Auth::id(),
        ]);

        // Transfer target
        $assassinRegistration->update([
            'qr_tag_target_user_id' => $victimRegistration->qr_tag_target_user_id,
        ]);

        return redirect()->route('event.show', $victimRegistration->event)
            ->with('success', __('Target tagged! You have a new target.'));
    }
}
