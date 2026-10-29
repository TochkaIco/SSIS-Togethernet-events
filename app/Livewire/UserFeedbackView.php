<?php

declare(strict_types=1);

namespace App\Livewire;

use App\FeedbackType;
use App\Models\Feedback;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Exceptions\CouldNotLoadImage;
use Spatie\Image\Image;

class UserFeedbackView extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(history: true)]
    public $search = '';

    public $image;

    public $feedback_image_path;

    public string $feedback_reply_image_path = '';

    public bool $filterResolved = false;

    public string $feedback_comment = '';

    public string $feedback_reply = '';

    public array $feedback_replies = [];

    public string $feedback_type = FeedbackType::FEATURE->value;

    public ?Feedback $selected_feedback = null;

    public function mount(): void
    {
        if (! Auth::user()->feedback()->exists()) {
            $this->redirect(route('home'));
        }
    }

    public function replyToFeedback(): void
    {
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
                'user_type' => 'user',
                'reply' => $this->feedback_reply,
                'image_path' => $feedback_reply_image_path ?? '',
                'timestamp' => now(),
            ];

            $this->selected_feedback->update([
                'replies' => $replies,
            ]);

            $this->reset('feedback_reply', 'image');

            Flux::toast(__('Feedback reply added. You may need to reload the page to see it.'), variant: 'success');

            GlobalLog::log('User replied to their feedback', 'feedback', ['feedback_id' => $this->selected_feedback->id, 'reply' => $this->feedback_reply]);
            GlobalLog::discord_log('green', 'User replied to their feedback', 'feedback', ['feedback_id', $this->selected_feedback->id, 'reply', $this->feedback_reply]);
        }
    }

    public function clearFeedbackImage(): void
    {
        $this->image = null;
    }

    public function openFeedbackModal(Feedback $feedback): void
    {
        if (! auth()->user()->feedback->contains($feedback)) {
            abort(403);
        }

        $this->selected_feedback = $feedback;
        $this->feedback_image_path = $feedback->image_path ?? null;
        $this->feedback_comment = $feedback->comment;
        $this->feedback_reply = '';
        $this->feedback_replies = $feedback->replies ?? [];
        $this->feedback_type = $feedback->type->value;
        $this->modal('feedback-modal-view')->show();
    }

    public function render(): View
    {
        return view('livewire.user-feedback-view', [
            'feedbacks' => Auth::user()
                ->feedback()
                ->with('user')
                ->when($this->search, fn ($query) => $query->where('comment', 'like', '%'.$this->search.'%'))
                ->when($this->filterResolved, fn ($query) => $query->where('is_finished', false))
                ->latest()
                ->paginate(10),
        ])
            ->title(__('My Feedback'))
            ->layoutData([
                'embedDescription' => 'Gå igenom bugg och feature reports du har skickat in.',
                'embedImagePath' => null,
            ]);
    }
}
