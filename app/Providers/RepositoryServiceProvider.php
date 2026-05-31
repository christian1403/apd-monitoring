<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\ItemRepository;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\LocationRepository;
use App\Repositories\Contracts\CameraRepositoryInterface;
use App\Repositories\CameraRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ItemRepositoryInterface::class,
            ItemRepository::class
        );

        $this->app->bind(
            LocationRepositoryInterface::class,
            LocationRepository::class
        );

        $this->app->bind(
            CameraRepositoryInterface::class,
            CameraRepository::class
        );
    }
}