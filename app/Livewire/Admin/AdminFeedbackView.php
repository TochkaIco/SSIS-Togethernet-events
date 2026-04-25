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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterResolved(): void
    {
        $this->resetPage();
    }

    public function markAsResolved(Feedback $feedback): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }
        $feedback->update(['is_finished' => true]);

        Flux::toast(__('Feedback marked as resolved.'), variant: 'success');
    }

    public function markAsUnresolved(Feedback $feedback): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }
        $feedback->update(['is_finished' => false]);

        Flux::toast(__('Feedback marked as unresolved.'), variant: 'success');
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
