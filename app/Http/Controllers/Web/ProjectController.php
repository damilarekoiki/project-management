<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\ProjectRepository;
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
    public function show(Request $request): InertiaResponse
    {
        //
        return Inertia::render('ProjectIndex', [
            'projects' => $this->projectRepository->getUserProjects(auth_user()->id),
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        //
        return Inertia::render('ProjectCreate');
    }
}
