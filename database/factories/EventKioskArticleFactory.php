<?php

/** @var Illuminate\Database\Eloquent\Factory \$factory */

namespace Database\Factories;

use App\Models\EventKioskArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventKioskArticle> */
class EventKioskArticleFactory extends Factory
{
    protected $model = EventKioskArticle::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'image_url' => $this->faker->optional()->imageUrl(640, 480, 'business', true, 'Faker'),
            'cost' => $this->faker->numberBetween(100, 5000),
            'amount' => $this->faker->numberBetween(2, 100),
            'category_id' => null,
            'kiosk_id' => null,
        ];
    }
}
