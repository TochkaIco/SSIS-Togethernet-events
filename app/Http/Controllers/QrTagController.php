<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ShuffleQrTagTargets;
use App\EventType;
use App\Models\EventUser;
use App\Models\QrTagLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrTagController extends Controller
{
    public function confirm(string $token): View|RedirectResponse
    {
        $victimRegistration = EventUser::where('qr_tag_token', $token)
            ->with(['event', 'user'])
            ->firstOrFail();

        if ($victimRegistration->event->event_type !== EventType::QR_TAG) {
            return redirect()->route('home')->with('error', __('Invalid event type.'));
        }

        if ($victimRegistration->qr_tag_tagged_at) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('This user has already been tagged.'));
        }

        if ($victimRegistration->is_disabled) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('This user is currently disabled.'));
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

        if ($assassinRegistration->is_disabled) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('You are currently disabled.'));
        }

        if ($assassinRegistration->qr_tag_target_user_id !== $victimRegistration->user_id) {
            return redirect()->route('event.show', $victimRegistration->event)
                ->with('error', __('This is not your target.'));
        }

        return view('qr_tag.confirm', [
            'victim' => $victimRegistration->user,
            'event' => $victimRegistration->event,
            'token' => $token,
        ]);
    }

    public function scan(string $token, ShuffleQrTagTargets $shuffleAction): RedirectResponse
    {
        return DB::transaction(function () use ($token, $shuffleAction) {
            $victimRegistration = EventUser::where('qr_tag_token', $token)
                ->with(['event', 'user'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($victimRegistration->event->event_type !== EventType::QR_TAG) {
                return redirect()->route('home')->with('error', __('Invalid event type.'));
            }

            if ($victimRegistration->qr_tag_tagged_at) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('This user has already been tagged.'));
            }

            if ($victimRegistration->is_disabled) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('This user is currently disabled.'));
            }

            $assassinRegistration = EventUser::where('event_id', $victimRegistration->event_id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (! $assassinRegistration) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('You are not registered for this event.'));
            }

            if ($assassinRegistration->qr_tag_tagged_at) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('You are already out of the game.'));
            }

            if ($assassinRegistration->is_disabled) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('You are currently disabled.'));
            }

            if ($assassinRegistration->qr_tag_target_user_id !== $victimRegistration->user_id) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('This is not your target.'));
            }

            if ($assassinRegistration->user_id === $victimRegistration->user_id) {
                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('error', __('You cannot tag yourself.'));
            }

            $newTargetId = $victimRegistration->qr_tag_target_user_id;

            // Successful tag
            $victimRegistration->update([
                'has_arrived' => true,
                'qr_tag_tagged_at' => now(),
                'qr_tag_tagged_by_user_id' => Auth::id(),
                'qr_tag_target_user_id' => null,
                'qr_tag_token' => Str::random(32),
            ]);

            QrTagLog::create([
                'event_id' => $victimRegistration->event_id,
                'user_id' => Auth::id(),
                'target_user_id' => $victimRegistration->user_id,
                'type' => 'tagged',
            ]);

            $assassinRegistration->update([
                'has_arrived' => true,
                'qr_tag_target_user_id' => $newTargetId,
            ]);

            $assassinRegistration->increment('qr_tag_count');

            // Prevent self-targeting by re-shuffling if there are other players left.
            if ($newTargetId === $assassinRegistration->user_id) {
                $activeCount = $victimRegistration->event->qrTagActiveParticipantsCount();

                if ($activeCount > 1) {
                    $shuffleAction->handle($victimRegistration->event, null, 'reshuffled');

                    return redirect()->route('event.show', $victimRegistration->event)
                        ->with('success', __('Target tagged! A loop was detected and targets have been re-shuffled.'));
                }

                // If only 1 player left, they won.
                $assassinRegistration->update(['qr_tag_target_user_id' => null]);

                return redirect()->route('event.show', $victimRegistration->event)
                    ->with('success', __('Target tagged! You are the last one standing!'));
            }

            return redirect()->route('event.show', $victimRegistration->event)
                ->with('success', __('Target tagged! You have a new target.'));
        });
    }
}
