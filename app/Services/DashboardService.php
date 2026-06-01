<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use App\Repositories\Contracts\DetectionRepositoryInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\CameraRepositoryInterface;

class DashboardService extends BaseService
{
    public function __construct(
        protected DetectionRepositoryInterface $detectionRepository,
        protected ItemRepositoryInterface $itemRepository,
        protected LocationRepositoryInterface $locationRepository,
        protected CameraRepositoryInterface $cameraRepository,
    ) {}

    public function getDashboardStats(): array
    {
        $safeCount = $this->detectionRepository->getCountByStatus('safe');
        $warningCount = $this->detectionRepository->getCountByStatus('warning');
        $unsafeCount = $this->detectionRepository->getCountByStatus('unsafe');

        return [
            'stats' => [
                'items' => $this->itemRepository->count(),
                'locations' => $this->locationRepository->count(),
                'cameras' => $this->cameraRepository->count(),
                'detections' => $this->detectionRepository->count(),
            ],
            'chartData' => [
                'safe' => $safeCount,
                'warning' => $warningCount,
                'unsafe' => $unsafeCount,
            ],
            'latestDetections' => $this->detectionRepository->getLatestDetections(10),
        ];
    }
}