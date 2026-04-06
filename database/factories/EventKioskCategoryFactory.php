<?php

/** @var Illuminate\Database\Eloquent\Factory\Factory $factory */

namespace Database\Factories;

use App\Models\EventKioskCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventKioskCategory> */
class EventKioskCategoryFactory extends Factory
{
    protected $model = EventKioskCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
