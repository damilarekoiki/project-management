<?php

namespace App\Repositories;

use App\DTOs\TaskFilterDto;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    private int $perPage = 20;

    /**
     * @return LengthAwarePaginator<int, Task>
     *                                         Get projects associated with a user.
     */
    public function getProjectTasks(int $project_id, int $user_id, ?TaskFilterDto $filters = null): LengthAwarePaginator
    {
        return Task::where('id', $project_id)
            ->where(function ($query) use ($user_id) {
                $query->where('assignee_id', $user_id)
                    ->orWhereHas('project', function ($query) use ($user_id) {
                        $query->where('creator_id', $user_id);
                    });
            })
            ->when($filters && $filters->hasFilters(), function ($query) use ($filters) {
                $query->filter($filters);
            })
            ->paginate($this->perPage);
    }

    /**
     * @param array<int, array{
     * assignee_id: int,
     * title: string,
     * status: string|null,
     * due_date: string|null
     * }> $tasks
     */
    public function createProjectTasks(Project $project, $tasks): void
    {
        $project->tasks()->createMany($tasks);
    }
}
