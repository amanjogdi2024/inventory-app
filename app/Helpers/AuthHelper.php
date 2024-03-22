<?php

namespace App\Helpers;

if (!function_exists('getAuthenticatedUser')) {
    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    function getAuthenticatedUser(Request $request)
    {
        $user =  auth()->user();
        return $user;
    }
}
