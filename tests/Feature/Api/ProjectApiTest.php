<?php

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
});

describe('API Project Authentication', function () {
    it('requires authentication for all project API endpoints', function () {
        $project = Project::factory()->create();

        // Test all endpoints without authentication
        $this->postJson(route('api.projects.store'))->assertUnauthorized();
        $this->patchJson(route('api.projects.update', $project))->assertUnauthorized();
        $this->deleteJson(route('api.projects.delete', $project))->assertUnauthorized();
    });

    it('returns user data when authenticated', function () {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJson([
                'id' => $this->adminUser->id,
                'name' => $this->adminUser->name,
                'email' => $this->adminUser->email,
            ]);
    });
});

describe('API Project Creation', function () {
    it('allows admin users to create projects', function () {
        $projectData = [
            'title' => 'New Project',
            'description' => 'Project description',
            'deadline' => '2025-12-31',
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson(route('api.projects.store'), $projectData);

        $response->assertOk();
        $this->assertDatabaseHas('projects', [
            'title' => 'New Project',
            'creator_id' => $this->adminUser->id,
        ]);
    });

    it('denies non-admin users from creating projects', function () {
        $projectData = [
            'title' => 'New Project',
            'description' => 'Project description',
            'deadline' => '2025-12-31',
        ];

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->postJson(route('api.projects.store'), $projectData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('projects', ['title' => 'New Project']);
    });

    it('validates required fields for project creation', function () {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson(route('api.projects.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });
});

describe('API Project Updates', function () {
    it('allows project creator (admin) to update their project', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $updateData = [
            'title' => 'Updated Project Title',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->patchJson(route('api.projects.update', $project), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Project Title',
        ]);
    });

    it('denies non-creator admin from updating project', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $updateData = ['title' => 'Updated by different admin'];

        $response = $this->actingAs($this->anotherAdminUser, 'sanctum')
            ->patchJson(route('api.projects.update', $project), $updateData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('projects', ['title' => 'Updated by different admin']);
    });

    it('denies non-admin users from updating projects', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $updateData = ['title' => 'Updated by non-admin'];

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->patchJson(route('api.projects.update', $project), $updateData);

        $response->assertForbidden();
    });
});

describe('API Project Deletion', function () {
    it('allows project creator (admin) to delete their project', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson(route('api.projects.delete', $project));

        $response->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    });

    it('denies non-creator admin from deleting project', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $response = $this->actingAs($this->anotherAdminUser, 'sanctum')
            ->deleteJson(route('api.projects.delete', $project));

        $response->assertForbidden();
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    });

    it('denies non-admin users from deleting projects', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->deleteJson(route('api.projects.delete', $project));

        $response->assertForbidden();
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    });
});

describe('API Project Access Control', function () {
    it('allows project creator to access their project via tasks endpoint', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $project));

        $response->assertOk();
    });

    it('allows assigned users to access project via tasks endpoint', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);
        Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => $this->nonAdminUser->id,
        ]);

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $project));

        $response->assertOk();
    });

    it('denies unrelated users from accessing project', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $response = $this->actingAs($this->nonAdminUser, 'sanctum')
            ->getJson(route('api.projects.tasks.show', $project));

        $response->assertForbidden();
    });
});
