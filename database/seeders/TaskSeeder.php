<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing projects or exit if none exist
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->info('No projects found. Please run ProjectSeeder first.');

            return;
        }

        // Get existing users or create some if none exist
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        // Create 3-7 tasks for each project
        foreach ($projects as $project) {
            $tasksCount = rand(3, 7);

            Task::factory()
                ->count($tasksCount)
                ->create([
                    'project_id' => $project->id,
                    'assignee_id' => fn () => $users->random()->id,
                ]);
        }
    }
}
