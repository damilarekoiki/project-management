<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProjectController as ProjectWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('/projects')->group(function () {
        Route::get('/', [ProjectWebController::class, 'index'])->name('projects');
        Route::get('/create', [ProjectWebController::class, 'create'])->name('projects.create');
        Route::get('/{project}', [ProjectWebController::class, 'show'])->name('projects.show');
        Route::get('/{project}/edit', [ProjectWebController::class, 'show'])->name('projects.edit');

    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
