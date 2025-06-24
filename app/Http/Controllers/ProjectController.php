<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\ProjectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        //
        /** @var User $user */
        $user = $request->user();
        $projects = $this->projectRepository->getUserProjects($user->id);

        return response()->json($projects, 200);
    }
}
