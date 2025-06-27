<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        /** @var array<string, mixed> $userData */
        $userData = User::factory()->raw([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::ADMIN->value,
        ]);

        User::updateOrCreate(
            ['email' => $userData['email']],
            $userData
        );

        User::factory(10)->create();

        // Run additional seeders
        $this->call([
            ProjectSeeder::class,
            TaskSeeder::class,
        ]);
    }
}
