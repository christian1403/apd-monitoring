<?php

namespace App\Infrastructure;

use App\Models\User;

abstract class BaseService
{
    /**
     * Get the currently authenticated user or null
     *
     * @return User|null
     */
    protected function user()
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user;
    }
}
