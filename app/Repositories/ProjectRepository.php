<?php

namespace App\Repositories;

use App\DTOs\ProjectDto;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    private int $perPage = 20;

    /**
     * Get count of all created projects.
     */
    public function getAllProjectsCount(): int
    {
        return Project::select('id')
            ->count();
    }

    /**
     * @return LengthAwarePaginator<int, Project>
     *                                            Get projects associated with a user.
     */
    public function getUserProjects(int $user_id): LengthAwarePaginator
    {
        return Project::with('creator')
            ->withCount('tasks')
            ->where('creator_id', $user_id)
            ->orWhereHas('tasks', function ($query) use ($user_id) {
                $query->where('assignee_id', $user_id);
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);
    }

    public function createProject(ProjectDto $projectData): Project
    {
        $project = Project::create([
            'creator_id' => $projectData->creator_id,
            'deadline' => $projectData->deadline,
            'description' => $projectData->description,
            'title' => $projectData->title,
        ]);

        return $project;
    }

    public function updateProject(Project $project, ProjectDto $projectData): Project
    {
        $project = tap($project)->update([
            'deadline' => $projectData->deadline,
            'description' => $projectData->description,
            'title' => $projectData->title,
        ]);

        return $project;
    }

    public function deleteProject(Project $project): void
    {
        $project
            ->delete();
    }

    /**
     * @return LengthAwarePaginator<int, Project>
     *                                            Get projects created by a user.
     */
    public function getCreatedProjects(int $user_id): LengthAwarePaginator
    {
        return Project::where('creator_id', $user_id)
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);
    }

    /**
     * @return LengthAwarePaginator<int, Project>
     *                                            Get projects assigned to a user.
     */
    public function getAssignedProjects(int $user_id): LengthAwarePaginator
    {
        return Project::with('creator')
            ->whereHas('tasks', function ($query) use ($user_id) {
                $query->where('assignee_id', $user_id);
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);
    }
}
