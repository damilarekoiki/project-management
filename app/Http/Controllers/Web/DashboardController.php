<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): InertiaResponse
    {
        // Delete the cache of tasks completed yesterday
        Cache::forget(Task::getCacheKeyCompletedYesterday());

        return Inertia::render('Dashboard', [
            'total_projects' => Cache::rememberForever('total-projects', function () {
                return Project::count();
            }),
            'total_tasks_completed_today' => Cache::rememberForever(Task::getCacheKeyCompletedToday(), function () {
                return Task::whereDate('completed_at', today())
                    ->count();
            }),
        ]);

    }
}
