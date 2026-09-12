<?php

namespace Database\Factories;

use App\Enums\ReviewDecision;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskReview>
 */
class TaskReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'reviewer_id' => User::factory()->employee(),
            'review_number' => 1,
            'decision' => fake()->randomElement(ReviewDecision::values()),
            'comment' => fake()->sentence(),
            'reviewed_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => ReviewDecision::Approved->value,
        ]);
    }

    public function changesRequested(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => ReviewDecision::ChangesRequested->value,
        ]);
    }
}
