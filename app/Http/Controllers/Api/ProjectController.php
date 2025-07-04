<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ProjectDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectStoreRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private TaskRepository $taskRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectRepository->getUserProjects(auth_user()->id);

        return response()->json($projects, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectStoreRequest $request): JsonResponse
    {
        Gate::authorize('create', Project::class);

        $projectData = new ProjectDto(
            creator_id: auth_user()->id,
            title: $request->safe()->string('title'),
            description: $request->safe()->string('description'),
            deadline: $request->safe()->date('deadline'),
        );

        $project = $this->projectRepository->createProject($projectData);

        /**
         * @var array<int, array{
         * assignee_id: int,
         * title: string,
         * status: string|null,
         * due_date: string|null
         * }> $tasks
         */
        $tasks = $request->safe()->array('tasks');
        $this->taskRepository->createProjectTasks($project, $tasks);

        return response()->json($project, 200);

    }

    public function update(Project $project, ProjectStoreRequest $request): JsonResponse
    {
        Gate::authorize('update', $project);

        $projectData = new ProjectDto(
            creator_id: auth_user()->id,
            title: $request->safe()->string('title'),
            description: $request->safe()->string('description'),
            deadline: $request->safe()->date('deadline'),
        );

        $project = $this->projectRepository->updateProject($project, $projectData);

        return response()->json($project, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        Gate::authorize('delete', $project);

        $this->projectRepository->deleteProject($project);

        return response()->json($project, 200);
    }
}
