<?php

namespace App\Providers;

use App\Repositories\CameraRepository;
use App\Repositories\Contracts\CameraRepositoryInterface;
use App\Repositories\Contracts\DetectionRepositoryInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\DetectionRepository;
use App\Repositories\ItemRepository;
use App\Repositories\LocationRepository;
use Illuminate\Support\ServiceProvider;

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

        $this->app->bind(
            DetectionRepositoryInterface::class,
            DetectionRepository::class
        );
    }
}
