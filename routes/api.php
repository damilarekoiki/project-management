<?php

use App\Http\Controllers\Api\ProjectController as ProjectApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('/projects')->group(function () {
        Route::delete('/{project}', [ProjectApiController::class, 'destroy'])->name('api.projects.delete');

    });
});
