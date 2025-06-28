<?php

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $this->nonAdminUser = User::factory()->create(['role' => UserRole::NON_ADMIN->value]);
    $this->anotherAdminUser = User::factory()->create(['role' => UserRole::ADMIN->value]);

    $this->project = Project::factory()->create(['creator_id' => $this->adminUser->id]);
    $this->anotherProject = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);
});

describe('API Task Authentication', function () {
    it('requires authentication for all task API endpoints', function () {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        // Test all endpoints without authentication
        $this->getJson(route('api.projects.tasks.show', $this->project))->assertUnauthorized();
        $this->putJson(route('api.projects.tasks.store', $this->project))->assertUnauthorized();
        $this->patchJson(route('api.projects.tasks.update', $this->project))->assertUnauthorized();
        $this->deleteJson(route('api.projects.tasks.delete', [$this->project, $task]))->assertUnauthorized();
    });
});

describe('API Task Creation', function () {
    it('allows admin users to create tasks in their projects', function () {
        $taskData = [
            'tasks' => [
                [
                    'title' => 'New Task',
                    'assignee_id' => $this->nonAdminUser->id,
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => '2025-12-31',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson(route('api.projects.tasks.store', $this->project->id), $taskData);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'project_id' => $this->project->id,
            'assignee_id' => $this->nonAdminUser->id,
            'status' => TaskStatus::PENDING->value,
            'due_date' => '2025-12-31',
        ]);
    });

    it('denies non-admin users from creating tasks', function () {
        $taskData = [
            'tasks' => [
                [
                    'title' => 'New Task',
                    'assignee_id' => $this->nonAdminUser->id,
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => '2025-12-31',
                ],
            ],
        ];

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->putJson(route('api.projects.tasks.store', $this->project), $taskData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('tasks', ['title' => 'New Task']);
    });

    it('denies admin users from creating tasks in projects they do not own', function () {
        $taskData = [
            'tasks' => [
                [
                    'title' => 'Unauthorized Task',
                    'assignee_id' => $this->nonAdminUser->id,
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => '2025-12-31',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson(route('api.projects.tasks.store', $this->anotherProject), $taskData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('tasks', ['title' => 'Unauthorized Task']);
    });

    it('validates required fields for task creation', function () {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson(route('api.projects.tasks.store', $this->project), ['tasks' => [
                [
                    'status' => TaskStatus::IN_PROGRESS->value,
                ],
            ]]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tasks.0.title']);
    });
});

describe('API Task Viewing', function () {
    it('allows project creator to view all project tasks', function () {
        Task::factory()->count(3)->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $this->project));

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('allows assigned users to view project tasks', function () {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'assignee_id' => $this->nonAdminUser->id,
        ]);
        Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $this->project));

        $response->assertOk();
    });

    it('denies unrelated users from viewing project tasks', function () {
        Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $this->project));

        $response->assertForbidden();
    });

    it('filters tasks by status when provided', function () {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => TaskStatus::PENDING,
        ]);
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => TaskStatus::DONE,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $this->project).'?status='.TaskStatus::PENDING->value);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('filters tasks by due date when provided', function () {
        $today = now()->format('Y-m-d');
        Task::factory()->create([
            'project_id' => $this->project->id,
            'due_date' => $today,
        ]);
        Task::factory()->create([
            'project_id' => $this->project->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $this->project).'?due_date='.$today);

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });
});

describe('API Task Updates', function () {
    it('allows updating tasks in owned project', function () {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $updateData = ['tasks' => [
            [
                'id' => $task->id,
                'title' => 'Updated Task Title',
                'status' => TaskStatus::IN_PROGRESS->value,
            ],
        ]];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson(route('api.projects.tasks.update', $this->project), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    });

    it('denies task updates for non-authorized users', function () {
        Task::factory()->create(['project_id' => $this->project->id]);

        $updateData = ['tasks' => [
            [
                'title' => 'Unauthorized Update',
            ],
        ]];

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->patchJson(route('api.projects.tasks.update', $this->project), $updateData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('tasks', ['title' => 'Unauthorized Update']);
    });
});

describe('API Task Deletion', function () {
    it('allows project creator (admin) to delete tasks from their project', function () {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson(route('api.projects.tasks.delete', [$this->project, $task]));

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });

    it('denies non-project-creator admin from deleting tasks', function () {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->anotherAdminUser, 'sanctum')
            ->deleteJson(route('api.projects.tasks.delete', [$this->project, $task]));

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    });

    it('denies non-admin users from deleting tasks', function () {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->deleteJson(route('api.projects.tasks.delete', [$this->project, $task]));

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    });

    it('prevents deletion of task from wrong project', function () {
        $task = Task::factory()->create(['project_id' => $this->anotherProject->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson(route('api.projects.tasks.delete', [$this->project, $task]));

        $response->assertNotFound();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    });
});
