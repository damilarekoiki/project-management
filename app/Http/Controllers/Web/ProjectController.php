<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function index(Request $request): InertiaResponse
    {
        //
        return Inertia::render('ProjectIndex', [
            'projects' => $this->projectRepository->getUserProjects(auth_user()->id),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, TaskRepository $taskRepository): InertiaResponse
    {
        //
        return Inertia::render('Project', [
            'project' => $project,
            'projectTasks' => $taskRepository->getProjectTasks($project, auth_user()->id),
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        //
        return Inertia::render('ProjectCreate');
    }
}
