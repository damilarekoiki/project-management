<?php

namespace App\Repositories;

use App\DTOs\TaskFilterDto;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;

class TaskRepository
{
    private int $perPage = 20;

    /** @var array<int, int> */
    private $persistingIds = [];

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
     * @param  array<int, array<string, mixed>>  $tasks
     */
    public function createProjectTasks(Project $project, $tasks): void
    {
        $project->tasks()->createMany($tasks);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tasks
     * @param  array<int, string>|null  $update
     */
    public function updateProjectTasks(Project $project, $tasks, $update = null): void
    {
        $project->tasks()->upsert(
            $tasks,
            ['id'],
            $update
        );
    }

    /**
     * @param array<int, array{
     * id?: int|null,
     * assignee_id?: int|null,
     * title?: string,
     * status?: string,
     * due_date?: string|null
     * }> $tasks
     * @return array<int, array<string, mixed>>
     */
    public function prepareTasksForPersistence($tasks): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = collect($tasks)->map(function ($task) {
            if (isset($task['id'])) {
                $this->persistingIds[] = $task['id'];
            }
            if (isset($task['status']) && $task['status'] === TaskStatus::DONE->value) {
                $task['completed_at'] = now();
            }

            return $task;
        })->toArray();

        return $data;
    }

    /**
     * @return array<int, int>
     */
    public function getPersistingIds(): array
    {
        return $this->persistingIds;
    }

    /**
     * @param  array<int, int>  $taskIds
     */
    public function canConfirmUserOwnership($taskIds, User $user): bool
    {
        return Task::whereIn('id', $taskIds)
            ->whereBelongsTo($user, 'assignee')
            ->exists();
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }
}
