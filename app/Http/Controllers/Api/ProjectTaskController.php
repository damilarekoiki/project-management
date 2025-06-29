<?php

namespace App\Http\Controllers\Api;

use App\DTOs\TaskFilterDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectTaskStoreRequest;
use App\Http\Requests\ProjectTaskUpdateRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ProjectTaskController extends Controller
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function index(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('view', $project);

        /** @var CarbonImmutable|null $dueDate */
        $dueDate = $request->date('due_date');

        // Create filter DTO from request parameters
        $filters = new TaskFilterDto(
            status: $request->string('status'),
            due_date: $dueDate
        );

        $tasks = $this->taskRepository->getProjectTasks($project, auth_user()->id, $filters);

        return response()->json($tasks, 200);
    }

    public function store(Project $project, ProjectTaskStoreRequest $storeRequest): JsonResponse
    {
        Gate::authorize('update', $project);
        /**
         * @var array<int, array{
         * id?: int|null,
         * assignee_id?: int|null,
         * title?: string,
         * status?: string,
         * due_date?: string|null
         * }> $tasks
         */
        $tasks = $storeRequest->safe()->array('tasks');
        $tasks = $this->taskRepository->prepareTasksForPersistence($tasks);

        $this->taskRepository->createProjectTasks($project, $tasks);

        return response()->json([], 200);
    }

    public function update(Project $project, ProjectTaskUpdateRequest $storeRequest): JsonResponse
    {
        /**
         * @var array<int, array{
         * id?: int|null,
         * assignee_id?: int|null,
         * title?: string,
         * status?: string,
         * due_date?: string|null
         * }> $tasks
         */
        $tasks = $storeRequest->safe()->array('tasks');
        $tasks = $this->taskRepository->prepareTasksForPersistence($tasks);

        $taskIds = $this->taskRepository->getPersistingIds();

        Gate::allowIf(fn (User $user) => $this->taskRepository->canConfirmUserOwnership($taskIds, $user) || ($user->isCreatorOfProject($project) && $user->isAdmin()));

        $update = auth_user()->isAdmin() ? null : ['status'];

        $this->taskRepository->updateProjectTasks($project, $tasks, $update);

        Cache::forget(Task::getCacheKeyCompletedToday());

        return response()->json([], 200);

    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $this->taskRepository->deleteTask($task);

        return response()->json(compact('task', 'project'), 200);
    }
}
