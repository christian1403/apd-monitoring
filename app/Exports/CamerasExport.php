<?php

namespace App\Exports;

use App\Services\CameraService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CamerasExport implements FromCollection, WithHeadings, WithMapping
{
    protected CameraService $cameraService;

    public function __construct(
        private readonly string $search = '',
        private readonly string $sortBy = 'created_at',
        private readonly string $sortDir = 'desc',
        private readonly array $where = [],
    ) {
        $this->cameraService = app(CameraService::class);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->cameraService->getExportData(
            search: $this->search,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
            where: $this->where,
        );
    }

    public function map($camera): array
    {
        static $counter = 1;

        return [
            $counter++,
            $camera->name,
            $camera->ip_address,
            $camera->location?->name ?? '-',
            ucfirst($camera->status),
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Name',
            'IP Address',
            'Location',
            'Status',
        ];
    }
}
