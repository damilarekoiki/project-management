<?php

use App\Http\Controllers\Api\ProjectController as ProjectApiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');

    Route::prefix('/projects')->group(function () {
        Route::post('/', [ProjectApiController::class, 'store'])->name('api.projects.store');
        Route::delete('/{project}', [ProjectApiController::class, 'destroy'])->name('api.projects.delete');
    });
});
