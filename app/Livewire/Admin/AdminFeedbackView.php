<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\FeedbackType;
use App\Models\Feedback;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AdminFeedbackView extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    public bool $filterResolved = false;

    public string $feedback_comment = '';

    public FeedbackType $feedback_type = FeedbackType::FEATURE;

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

    public function markAsResolved($feedbackId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }
        $feedback = Feedback::findOrFail($feedbackId);
        $feedback->update([
            'is_finished' => true,
            'is_rejected' => false,
        ]);

        $this->modal('feedback-modal-admin')->close();

        Flux::toast(__('Feedback marked as resolved.'), variant: 'success');
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
        $this->feedback_type = $feedback->type;
        $this->modal('feedback-modal-admin')->show();
    }

    public function updateFeedback(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->validate([
            'feedback_comment' => 'required|string|min:5',
            'feedback_type' => 'required',
        ]);

        $this->selected_feedback->update([
            'comment' => $this->feedback_comment,
            'type' => $this->feedback_type,
        ]);

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

        $this->modal('confirm-feedback-deletion')->close();

        Flux::toast(__('Feedback deleted.'), variant: 'success');
    }

    #[Layout('layouts.app', ['title' => 'Admin Feedback'])]
    public function render()
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
