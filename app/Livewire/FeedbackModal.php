<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Feedback;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Exceptions\CouldNotLoadImage;
use Spatie\Image\Image;

class FeedbackModal extends Component
{
    use WithFileUploads;

    public $type = 'bug'; // Default selection

    public $image;

    public string $image_path = '';

    public $comment = '';

    public bool $anonymous = false;

    public function rules(): array
    {
        return [
            'type' => 'required|in:bug,feature,qol',
            'image' => [
                'nullable',
                File::types(['avif', 'jpeg', 'jpg', 'png', 'webp'])
                    ->max(12288),
            ],
            'comment' => 'required|min:5|max:5000',
            'anonymous' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->image) {
            $originalPath = $this->image->store('feedback', 'public');
            $originalFull = Storage::disk('public')->path($originalPath);
            $webpPath = pathinfo($originalPath, PATHINFO_DIRNAME).'/'.pathinfo($originalPath, PATHINFO_FILENAME).'.webp';
            try {
                Image::load($originalFull)
                    ->fit(Fit::Max, 1200, 900)
                    ->format('webp')
                    ->save(Storage::disk('public')->path($webpPath));

                $imagePath = $webpPath;
            } catch (CouldNotLoadImage $e) {
                Flux::toast(__('Could not load your image. Try again...').$e, variant: 'error');
            }

            // Delete original upload
            Storage::disk('public')->delete($originalPath);
        }

        if (isset($imagePath)) {
            $feedback = Feedback::create([
                'type' => $this->type,
                'comment' => $this->comment,
                'image_path' => $imagePath,
                'user_id' => $this->anonymous ? null : auth()->id(),
            ]);
        } else {
            $feedback = Feedback::create([
                'type' => $this->type,
                'comment' => $this->comment,
                'user_id' => $this->anonymous ? null : auth()->id(),
            ]);
        }

        $this->reset(['comment', 'type', 'image', 'anonymous']);
        Flux::modal('feedback-modal')->close();
        Flux::toast(__('Feedback submitted. Thanks!'), variant: 'success');
        GlobalLog::log('New feedback submitted', 'feedback', ['feedback_id' => $feedback->id]);
        GlobalLog::discord_log('blue', 'New feedback submitted', 'feedback', ['feedback_id', $feedback->id, 'type', $feedback->type->label(), 'comment', Str::limit($feedback->comment, 245, '...')]);
    }

    public function clearFeedbackImage(): void
    {
        $this->image = null;
    }

    public function render(): View
    {
        return view('livewire.feedback-modal');
    }
}
