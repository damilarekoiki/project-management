<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users or create one if none exist
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory()->count(3)->create();
        }

        // Create 10 projects assigned to random users
        Project::factory()
            ->count(10)
            ->create([
                'creator_id' => fn () => $users->random()->id,
            ]);
    }
}
