<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'course_id' => Course::factory(),
            'rating'    => fake()->numberBetween(1, 5),
            'comment'   => fake()->optional()->sentence(),
        ];
    }
}
