<?php

namespace Database\Factories;

use App\Models\PantAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PantAlert>
 */
class PantAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_complete' => false,
            'completed_by' => null,
            'sek_received' => null,
            'admin_user_id' => null,
            'receiver_swish' => '1234567890',
        ];
    }

    public function completed(array $completedBy, float $sekReceived, ?string $receiptPath = null): self
    {
        return $this->state(fn (array $attributes) => [
            'is_complete' => true,
            'completed_by' => $completedBy,
            'sek_received' => $sekReceived,
            'receipt_path' => $receiptPath,
        ]);
    }
}
