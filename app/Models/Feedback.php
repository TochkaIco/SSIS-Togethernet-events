<?php

declare(strict_types=1);

namespace App\Models;

use App\FeedbackType;
use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'comment', 'is_finished'])]
class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory;

    protected $casts = [
        'type' => FeedbackType::class,
    ];
}
