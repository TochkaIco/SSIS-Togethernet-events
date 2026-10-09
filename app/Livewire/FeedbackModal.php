<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Feedback;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;

class FeedbackModal extends Component
{
    public $type = 'bug'; // Default selection

    public $comment = '';

    public bool $anonymous = false;

    protected $rules = [
        'type' => 'required|in:bug,feature,qol',
        'comment' => 'required|min:5|max:5000',
        'anonymous' => 'boolean',
    ];

    public function save(): void
    {
        $this->validate();

        $feedback = Feedback::create([
            'type' => $this->type,
            'comment' => $this->comment,
            'user_id' => $this->anonymous ? null : auth()->id(),
        ]);

        $this->reset(['comment', 'type', 'anonymous']);
        Flux::modal('feedback-modal')->close();
        Flux::toast(__('Feedback submitted. Thanks!'), variant: 'success');
        GlobalLog::log('New feedback submitted', 'feedback', ['feedback_id' => $feedback->id]);
        GlobalLog::discord_log('New feedback submitted', 'feedback', ['feedback_id', $feedback->id, 'type', $feedback->type->label(), 'comment', Str::limit($feedback->comment, 120, '...')]);
    }

    public function render()
    {
        return view('livewire.feedback-modal');
    }
}
