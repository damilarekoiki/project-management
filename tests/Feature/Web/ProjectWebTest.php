<?php

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create(['role' => UserRole::ADMIN->value]);
    $this->nonAdminUser = User::factory()->create(['role' => UserRole::NON_ADMIN->value]);
    $this->anotherAdminUser = User::factory()->create(['role' => UserRole::ADMIN->value]);
});

describe('Web Project Authentication', function () {
    it('redirects unauthenticated users to login', function () {
        $project = Project::factory()->create();

        $this->get(route('projects'))->assertRedirect(route('login'));
        $this->get(route('projects.create'))->assertRedirect(route('login'));
        $this->get(route('projects.show', $project))->assertRedirect(route('login'));
        $this->get(route('projects.edit', $project))->assertRedirect(route('login'));
    });

    it('allows authenticated users to access projects index', function () {
        $this->actingAs($this->adminUser)
            ->get(route('projects'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ProjectIndex'));
    });
});

describe('Web Projects Index Page', function () {
    it('displays projects for authenticated users', function () {
        $ownProject = Project::factory()->create(['creator_id' => $this->adminUser->id]);
        $assignedProject = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);

        // Create a task to make the user assigned to the project
        Task::factory()->create([
            'project_id' => $assignedProject->id,
            'assignee_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(route('projects'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ProjectIndex')
                ->has('projects.data', 2)
                ->where('projects.data.0.id', $ownProject->id)
                ->where('projects.data.1.id', $assignedProject->id)
            );
    });

    it('shows only projects user has access to', function () {
        $ownProject = Project::factory()->create(['creator_id' => $this->adminUser->id]);
        $inaccessibleProject = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);

        $this->actingAs($this->adminUser)
            ->get(route('projects'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ProjectIndex')
                ->has('projects.data', 1)
                ->where('projects.data.0.id', $ownProject->id)
            );
    });

    it('shows empty state when user has no accessible projects', function () {
        Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);

        $this->actingAs($this->nonAdminUser)
            ->get(route('projects'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ProjectIndex')
                ->has('projects.data', 0)
            );
    });
});

describe('Web Project Creation Page', function () {
    it('allows admin users to access project creation page', function () {
        $this->actingAs($this->adminUser)
            ->get(route('projects.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ProjectCreate'));
    });

    it('denies non-admin users from accessing project creation page', function () {
        $this->actingAs($this->nonAdminUser)
            ->get(route('projects.create'))
            ->assertForbidden();
    });
});

describe('Web Project Show Page', function () {
    it('allows project creator to view their project', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $this->actingAs($this->adminUser)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Project')
                ->where('project.id', $project->id)
                ->where('project.title', $project->title)
            );
    });

    it('allows assigned users to view project', function () {
        $project = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);
        Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => $this->nonAdminUser->id,
        ]);

        $this->actingAs($this->nonAdminUser)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Project')
                ->where('project.id', $project->id)
            );
    });

    it('denies unrelated users from viewing project', function () {
        $project = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);

        $this->actingAs($this->nonAdminUser)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    });

    it('includes project tasks and related data', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);
        $tasks = Task::factory()->count(3)->create(['project_id' => $project->id]);

        $this->actingAs($this->adminUser)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Project')
                ->where('project.id', $project->id)
                ->has('projectTasks.data', 3)
            );
    });
});

describe('Web Project Edit Page', function () {
    it('allows project creator (admin) to access edit page', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $this->actingAs($this->adminUser)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Project')
                ->where('project.id', $project->id)
            );
    });

    it('denies non-creator admin from accessing edit page', function () {
        $project = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);

        $this->actingAs($this->adminUser)
            ->get(route('projects.edit', $project))
            ->assertForbidden();
    });

    it('denies non-admin users from accessing edit page', function () {
        $project = Project::factory()->create(['creator_id' => $this->adminUser->id]);

        $this->actingAs($this->nonAdminUser)
            ->get(route('projects.edit', $project))
            ->assertForbidden();
    });
});

describe('Web Project Authorization Edge Cases', function () {
    it('handles non-existent project gracefully', function () {
        $this->actingAs($this->adminUser)
            ->get(route('projects.show', 999))
            ->assertNotFound();
    });

    it('maintains authorization for users with multiple roles', function () {
        // Test that admin user who is also assigned to project can access it
        $project = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);
        Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(route('projects.show', $project))
            ->assertOk();
    });

    it('enforces authorization when user loses access', function () {
        $project = Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => $this->nonAdminUser->id,
        ]);

        // User should have access initially
        $this->actingAs($this->nonAdminUser)
            ->get(route('projects.show', $project))
            ->assertOk();

        // Remove the task (user loses access)
        $task->delete();

        // User should no longer have access
        $this->actingAs($this->nonAdminUser)
            ->get(route('projects.show', $project))
            ->assertForbidden();
    });
});

describe('Web Dashboard Integration', function () {
    it('allows authenticated users to access projects page', function () {
        $this->actingAs($this->adminUser)
            ->get(route('projects'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ProjectIndex'));
    });

    it('redirects unauthenticated users from projects page', function () {
        $this->get(route('projects'))
            ->assertRedirect(route('login'));
    });

    it('dashboard shows user-specific data', function () {
        Project::factory()->create(['creator_id' => $this->adminUser->id]);
        Project::factory()->create(['creator_id' => $this->anotherAdminUser->id]);

        $this->actingAs($this->adminUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard')
                ->has('total_projects')
                ->has('total_tasks_completed_today')
            );
    });
});
