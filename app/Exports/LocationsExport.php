<?php

namespace App\Exports;

use app\Services\LocationService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LocationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected LocationService $locationService;

    public function __construct(
        private readonly string $search = '',
        private readonly string $sortBy = 'created_at',
        private readonly string $sortDir = 'desc',
        private readonly array $where = [],
    ) {
        $this->locationService = app(LocationService::class);
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->locationService->getExportData(
            search: $this->search,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
            where: $this->where
        );
    }

    public function map($location): array
    {
        // increment counter for each row
        static $counter = 1;

        return [
            $counter++,
            $location->name,
            $location->description,
            $location->address,
            $location->latitude,
            $location->longitude,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Name',
            'Description',
            'Address',
            'Latitude',
            'Longitude',
        ];
    }
}
