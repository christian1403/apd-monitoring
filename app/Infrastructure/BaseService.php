<?php

namespace App\Infrastructure;

abstract class BaseService
{
    /**
     * Get the currently authenticated user or null
     *
     * @return \App\Models\User|null
     */
    protected function user()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user;
    }
}