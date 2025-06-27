<?php

namespace App\Http\Controllers\Api;

use App\DTOs\TaskFilterDto;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function index(Request $request, Project $project): JsonResponse
    {
        // Create filter DTO from request parameters
        $filters = new TaskFilterDto(
            status: $request->string('status'),
            due_date: $request->date('due_date')
        );

        $tasks = $this->taskRepository->getProjectTasks($project, auth_user()->id, $filters);

        return response()->json($tasks, 200);
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        $this->taskRepository->deleteTask($task);

        return response()->json(compact('task', 'project'), 200);
    }

    // Todo: update task, delete task, create, new tasks
}
