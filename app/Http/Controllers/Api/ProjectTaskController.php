<?php

namespace App\Http\Controllers\Api;

use App\DTOs\TaskFilterDto;
use App\Http\Controllers\Controller;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    //

    public function __construct(private TaskRepository $taskRepository) {}

    public function index(Request $request, int $project_id): JsonResponse
    {
        //
        // Create filter DTO from request parameters
        $filters = new TaskFilterDto(
            status: $request->string('status'),
            due_date: $request->date('due_date')
        );

        $tasks = $this->taskRepository->getProjectTasks($project_id, auth_user()->id, $filters);

        return response()->json($tasks, 200);
    }

    // Todo: update task
}
