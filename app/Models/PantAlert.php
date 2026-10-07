<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PantAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property bool $is_complete
 * @property array<int>|null $completed_by
 * @property float $sek_received
 * @property int|null $admin_user_id
 * @property string $receiver_swish
 * @property string|null $receipt_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $admin
 * @property-read Collection<int, User> $completedByUsers
 */
#[Fillable(['is_complete', 'completed_by', 'sek_received', 'admin_user_id', 'receiver_swish', 'receipt_path'])]
class PantAlert extends Model
{
    /** @use HasFactory<PantAlertFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'completed_by' => 'array',
            'is_complete' => 'boolean',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    protected function completedByUsers(): Attribute
    {
        return Attribute::make(get: function () {
            return User::whereIn('id', $this->completed_by ?? [])->get();
        });
    }

    #[Scope]
    protected function active($query)
    {
        return $query->where('is_complete', false);
    }
}
