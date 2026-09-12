<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'department_id' => null,
            'status' => fake()->randomElement(TaskStatus::values()),
            'priority' => fake()->randomElement(TaskPriority::values()),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'start_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'created_by' => User::factory(),
            'estimated_hours' => fake()->randomFloat(2, 1, 40),
            'actual_hours' => fake()->randomFloat(2, 0, 40),
            'sort_order' => 0,
            'updates_count' => 0,
            'resubmissions_count' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Task $task) {
            if (! $task->assignees()->exists()) {
                $assignee = User::factory()->employee()->create();
                $task->assignees()->attach($assignee->id, ['assigned_at' => now()]);
            }
        });
    }

    public function asNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::New->value,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::UnderReview->value,
            'updates_count' => 1,
            'submitted_at' => now(),
        ]);
    }

    public function changesRequested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::ChangesRequested->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Completed->value,
            'review_decision' => Task::REVIEW_APPROVED,
            'reviewed_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::OnHold->value,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Cancelled->value,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::High->value,
        ]);
    }
}
