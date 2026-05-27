<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\ItemRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ItemRepositoryInterface::class,
            ItemRepository::class
        );
    }
}