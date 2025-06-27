<?php

namespace App\Repositories;

use App\DTOs\TaskFilterDto;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Pagination\CursorPaginator;

class TaskRepository
{
    private int $perPage = 20;

    /**
     * @return CursorPaginator<int, Task>
     *                                    Get tasks of a project associated with a user.
     */
    public function getProjectTasks(Project $project, int $user_id, ?TaskFilterDto $filters = null): CursorPaginator
    {
        return $project->tasks()
            ->where(function ($query) use ($user_id) {
                $query->where('assignee_id', $user_id)
                    ->orWhereHas('project', function ($query) use ($user_id) {
                        $query->where('creator_id', $user_id);
                    })
                    ->with('assignee:id,name');
            })
            ->when($filters && $filters->hasFilters(), function ($query) use ($filters) {
                $query->filter($filters);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->cursorPaginate($this->perPage);
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
        $project->tasks()->createMany($this->prepareTasksForPersistence($tasks));
    }

    /**
     * @param array<int, array{
     * id: int,
     * assignee_id: int,
     * title: string,
     * status: string|null,
     * due_date: string|null
     * }> $tasks
     */
    public function updateProjectTasks(Project $project, $tasks): void
    {
        $project->tasks()->upsert(
            $this->prepareTasksForPersistence($tasks),
            ['id']
        );
    }

    /**
     * @param array<int, array{
     * id?: int,
     * assignee_id: int,
     * title: string,
     * status: string|null,
     * due_date: string|null
     * }> $tasks
     * @return array<int, array<string, mixed>>
     */
    protected function prepareTasksForPersistence($tasks): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = collect($tasks)->map(function ($task) {
            if ($task['status'] === TaskStatus::DONE->value) {
                $task['completed_at'] = now();
            }

            return $task;
        })->toArray();

        return $data;
    }

    public function deleteTask(Task $task): void
    {
        $task
            ->delete();
    }
}
