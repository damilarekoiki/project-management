<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(TaskStatus::cases());
        $completedAt = null;

        if ($status === TaskStatus::DONE) {
            $completedAt = fake()->dateTimeBetween('-1 month', 'now');
        }

        return [
            'project_id' => Project::factory(),
            'assignee_id' => User::factory(),
            'title' => fake()->sentence(4),
            'status' => $status,
            'due_date' => fake()->dateTimeBetween('now', '+2 months'),
            'completed_at' => $completedAt,
        ];
    }
}
