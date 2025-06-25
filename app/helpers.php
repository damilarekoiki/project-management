<?php

use App\Models\User;

if (! function_exists('auth_user')) {
    function auth_user(): User
    {
        /** @var User $user */
        $user = request()->user();

        return $user;
    }
}
