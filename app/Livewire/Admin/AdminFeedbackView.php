<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\FeedbackType;
use App\Models\Feedback;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Exceptions\CouldNotLoadImage;
use Spatie\Image\Image;

class AdminFeedbackView extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(history: true)]
    public $search = '';

    public bool $filterResolved = false;

    public string $feedback_comment = '';

    public $image;

    public $feedback_image_path;

    public string $feedback_reply = '';

    public array $feedback_replies = [];

    public string $feedback_type = FeedbackType::FEATURE->value;

    public ?Feedback $selected_feedback = null;

    public $feedbackToDelete;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterResolved(): void
    {
        $this->resetPage();
    }

    public function replyToFeedback(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        if ($this->feedback_reply) {
            $replies = $this->selected_feedback->replies ?? [];

            if ($this->image) {
                $originalPath = $this->image->store('feedback', 'public');
                $originalFull = Storage::disk('public')->path($originalPath);
                $webpPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.pathinfo($originalPath, PATHINFO_FILENAME).'.webp';
                try {
                    Image::load($originalFull)
                        ->fit(Fit::Max, 1200, 900)
                        ->format('webp')
                        ->save(Storage::disk('public')->path($webpPath));

                    $feedback_reply_image_path = $webpPath;
                } catch (CouldNotLoadImage $e) {
                    Flux::toast(__('Could not load your image. Try again...').$e, variant: 'error');
                }

                // Delete original upload
                Storage::disk('public')->delete($originalPath);
            }

            $replies[] = [
                'user_type' => 'admin',
                'reply' => $this->feedback_reply,
                'image_path' => $feedback_reply_image_path ?? '',
                'timestamp' => now(),
            ];

            $this->selected_feedback->update([
                'replies' => $replies,
            ]);

            if ($this->selected_feedback->is_finished) {
                $this->selected_feedback->update([
                    'is_finished' => false,
                ]);

                Flux::toast(__('Feedback reply added. You may need to reload the page to see it.'), variant: 'success');
                GlobalLog::log('Admin replied to a closed feedback ticket, the ticket has been re-opened', 'feedback', ['feedback_id' => $this->selected_feedback->id, 'reply' => $this->feedback_reply]);
                GlobalLog::discord_log('yellow', 'Admin replied to a closed feedback ticket, the ticket has been re-opened', 'feedback', ['feedback_id', $this->selected_feedback->id, 'reply', $this->feedback_reply]);
            } else {
                Flux::toast(__('Feedback reply added. You may need to reload the page to see it.'), variant: 'success');
                GlobalLog::log('Admin replied to an open feedback ticket', 'feedback', ['feedback_id' => $this->selected_feedback->id, 'reply' => $this->feedback_reply]);
                GlobalLog::discord_log('green', 'Admin replied to an open feedback ticket', 'feedback', ['feedback_id', $this->selected_feedback->id, 'reply', $this->feedback_reply]);
            }

            $this->reset('feedback_reply', 'image');
        }
    }

    public function leaveFeedbackReplyOnStatusUpdate(Feedback $feedback): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->modal('feedback-reply-admin')->close();

        if ($this->feedback_reply) {
            $replies = $feedback->replies ?? [];

            if ($this->image) {
                $originalPath = $this->image->store('feedback', 'public');
                $originalFull = Storage::disk('public')->path($originalPath);
                $webpPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.pathinfo($originalPath, PATHINFO_FILENAME).'.webp';
                try {
                    Image::load($originalFull)
                        ->fit(Fit::Max, 1200, 900)
                        ->format('webp')
                        ->save(Storage::disk('public')->path($webpPath));

                    $feedback_reply_image_path = $webpPath;
                } catch (CouldNotLoadImage $e) {
                    Flux::toast(__('Could not load your image. Try again...').$e, variant: 'error');
                }

                // Delete original upload
                Storage::disk('public')->delete($originalPath);
            }

            $replies[] = [
                'user_type' => 'admin',
                'reply' => $this->feedback_reply,
                'image_path' => $feedback_reply_image_path ?? '',
                'timestamp' => now(),
            ];

            $feedback->update([
                'replies' => $replies,
            ]);

            Flux::toast(__('Feedback reply added.'), variant: 'success');
            GlobalLog::log('Admin replied to feedback', 'feedback', ['feedback_id' => $feedback->id, 'reply' => $this->feedback_reply]);
            GlobalLog::discord_log('green', 'Admin replied to feedback', 'feedback', ['feedback_id', $feedback->id, 'reply', $this->feedback_reply]);

            $this->reset('feedback_reply', 'image');
        }
    }

    public function markAsResolved($feedbackId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }
        $this->modal('feedback-modal-admin')->close();

        $feedback = Feedback::findOrFail($feedbackId);

        $feedback->update([
            'is_finished' => true,
            'is_rejected' => false,
        ]);

        GlobalLog::log('Feedback marked as resolved', 'feedback', ['feedback_id' => $feedback->id]);
        GlobalLog::discord_log('green', 'Feedback marked as resolved', 'feedback', ['feedback_id', $feedback->id, 'type', $feedback->type->label(), 'comment', Str::limit($feedback->comment, 245, '...')]);

        Flux::toast(__('Feedback marked as resolved.'), variant: 'success');

        $this->openFeedbackReplyModal($feedback);
    }

    public function openFeedbackReplyModal(Feedback $feedback): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->selected_feedback = $feedback;
        $this->feedback_comment = $feedback->comment;
        $this->feedback_image_path = $feedback->image_path ?? null;
        $this->feedback_type = $feedback->type->value;
        $this->feedback_reply = '';

        $this->modal('feedback-reply-admin')->show();
    }

    public function markAsUnresolved($feedbackId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update([
            'is_finished' => false,
            'is_rejected' => false,
        ]);

        GlobalLog::log('Feedback marked as unresolved', 'feedback', ['feedback_id' => $feedback->id]);
        GlobalLog::discord_log('yellow', 'Feedback marked as unresolved', 'feedback', ['feedback_id', $feedback->id, 'type', $feedback->type->label(), 'comment', Str::limit($feedback->comment, 245, '...')]);

        $this->modal('feedback-modal-admin')->close();

        Flux::toast(__('Feedback marked as unresolved.'), variant: 'success');
    }

    public function markAsRejected($feedbackId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update([
            'is_finished' => true,
            'is_rejected' => true,
        ]);

        GlobalLog::log('Feedback marked as rejected', 'feedback', ['feedback_id' => $feedback->id]);
        GlobalLog::discord_log('yellow', 'Feedback marked as rejected', 'feedback', ['feedback_id', $feedback->id, 'type', $feedback->type->label(), 'comment', Str::limit($feedback->comment, 245, '...')]);

        $this->modal('feedback-modal-admin')->close();

        Flux::toast(__('Feedback marked as not implementing.'), variant: 'success');
    }

    public function openUserFeedbackModal(Feedback $feedback): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->selected_feedback = $feedback;
        $this->feedback_comment = $feedback->comment;
        $this->feedback_image_path = $feedback->image_path ?? null;
        $this->feedback_reply = '';
        $this->feedback_replies = $feedback->replies ?? [];
        $this->feedback_type = $feedback->type->value;
        $this->modal('feedback-view-modal-admin')->show();
    }

    public function openUserUpdateFeedbackModal(Feedback $feedback): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->selected_feedback = $feedback;
        $this->feedback_comment = $feedback->comment;
        $this->feedback_replies = $feedback->replies ?? [];
        $this->feedback_type = $feedback->type->value;
        $this->modal('feedback-update-modal-admin')->show();
    }

    public function updateFeedback(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->validate([
            'feedback_comment' => 'required|string|min:5',
            'feedback_type' => 'required|in:bug,feature,qol',
        ]);

        $this->selected_feedback->update([
            'comment' => $this->feedback_comment,
            'type' => $this->feedback_type,
        ]);

        GlobalLog::log('User feedback updated by admin', 'feedback', ['feedback_id' => $this->selected_feedback->id]);
        GlobalLog::discord_log('blue', 'User feedback updated by admin', 'feedback', ['feedback_id', $this->selected_feedback->id, 'type', $this->selected_feedback->type->label(), 'comment' => Str::limit($this->selected_feedback->comment, 120, '...')]);

        $this->modal('feedback-modal-admin')->close();

        Flux::toast(__('Feedback updated successfully.'), variant: 'success');
    }

    public function confirmDelete($feedbackId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->feedbackToDelete = $feedbackId;
        $this->modal('confirm-feedback-deletion')->show();
    }

    public function deleteFeedback(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $feedback = Feedback::findOrFail($this->feedbackToDelete);
        $feedback->delete();

        GlobalLog::log('Feedback deleted by admin', 'feedback', ['feedback_id' => $feedback->id]);
        GlobalLog::discord_log('yellow', 'Feedback deleted by admin', 'feedback', ['feedback_id' => $feedback->id, 'type', $feedback->type->label(), 'comment', Str::limit($feedback->comment, 245, '...')]);

        $this->modal('confirm-feedback-deletion')->close();

        Flux::toast(__('Feedback deleted.'), variant: 'success');
    }

    #[Layout('layouts.app', ['title' => 'Admin Feedback'])]
    public function render(): View
    {
        return view('livewire.admin.admin-feedback-view', [
            'feedbacks' => Feedback::query()
                ->with('user')
                ->when($this->search, fn ($query) => $query->where('comment', 'like', '%'.$this->search.'%'))
                ->when($this->filterResolved, fn ($query) => $query->where('is_finished', false))
                ->latest()
                ->paginate(10),
        ]);
    }
}
