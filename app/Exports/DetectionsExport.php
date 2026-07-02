<?php

namespace App\Exports;

use App\Services\DetectionService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DetectionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected DetectionService $detectionService;

    public function __construct(
        private readonly string $search = '',
        private readonly string $sortBy = 'created_at',
        private readonly string $sortDir = 'desc',
        private readonly array $where = [],
    ) {
        $this->detectionService = app(DetectionService::class);
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->detectionService->getExportData(
            search: $this->search,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
            where: $this->where,
        );
    }

    public function map($detection): array
    {
        static $counter = 1;

        return [
            $counter++,
            $detection->detectionItems->map(fn ($di) => $di->item?->name)->filter()->implode(', ') ?: '-',
            $detection->camera?->name ?? '-',
            $detection->camera?->ip_address ?? '-',
            $detection->location?->name ?? '-',
            ucfirst($detection->status),
            $detection->detected_at ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Item',
            'Camera',
            'IP Address',
            'Location',
            'Status',
            'Detected At',
        ];
    }
}
