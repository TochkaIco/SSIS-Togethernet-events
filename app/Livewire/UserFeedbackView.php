<?php

declare(strict_types=1);

namespace App\Livewire;

use App\FeedbackType;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserFeedbackView extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    public bool $filterResolved = false;

    public string $feedback_comment = '';

    public FeedbackType $feedback_type = FeedbackType::FEATURE;

    public ?Feedback $selected_feedback = null;

    public function mount(): void
    {
        if (! Auth::user()->feedback()->exists()) {
            $this->redirect(route('home'));
        }
    }

    public function openFeedbackModal(Feedback $feedback): void
    {
        if (! auth()->user()->feedback->contains($feedback)) {
            abort(403);
        }

        $this->selected_feedback = $feedback;
        $this->feedback_comment = $feedback->comment;
        $this->feedback_type = $feedback->type;
        $this->modal('feedback-modal-view')->show();
    }

    public function render()
    {
        return view('livewire.user-feedback-view', [
            'feedbacks' => Auth::user()
                ->feedback()
                ->with('user')
                ->when($this->search, fn ($query) => $query->where('comment', 'like', '%'.$this->search.'%'))
                ->when($this->filterResolved, fn ($query) => $query->where('is_finished', false))
                ->latest()
                ->paginate(10),
        ])->title(__('My Feedback'));
    }
}
