<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Feedback;
use Flux\Flux;
use Livewire\Component;

class FeedbackModal extends Component
{
    public $type = 'bug'; // Default selection

    public $comment = '';

    protected $rules = [
        'type' => 'required|in:bug,feature,qol',
        'comment' => 'required|min:5|max:255',
    ];

    public function save(): void
    {
        $this->validate();

        Feedback::create([
            'type' => $this->type,
            'comment' => $this->comment,
        ]);

        $this->reset(['comment', 'type']);
        Flux::modal('feedback-modal')->close();
        Flux::toast(__('Feedback submitted. Thanks!'), variant: 'success');
    }

    public function render()
    {
        return view('livewire.feedback-modal');
    }
}
