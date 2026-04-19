<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\FeedbackType;
use App\Models\Feedback;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class AdminFeedbackView extends Component
{
    use WithPagination;

    public string $feedback_comment = '';

    public FeedbackType $feedback_type = FeedbackType::FEATURE;

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

        $this->feedback_comment = $feedback->comment;
        $this->feedback_type = $feedback->type;
        $this->modal('feedback-modal')->show();
    }

    public function render()
    {
        return view('livewire.admin.admin-feedback-view', [
            'feedbacks' => Feedback::latest()->paginate(10),
        ]);
    }
}
