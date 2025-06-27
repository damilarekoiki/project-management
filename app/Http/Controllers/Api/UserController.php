<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Search for users by name
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('query');

        if (blank($query)) {
            return response()->json([], 200);
        }

        $users = User::select(['id', 'name'])
            ->whereLike('name', "%{$query}%")
            ->limit(4)
            ->get();

        return response()->json($users, 200);
    }
}
