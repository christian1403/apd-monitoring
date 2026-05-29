<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use app\Services\ItemService;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected ItemService $itemService;
    public function __construct(
        private readonly string $search = '',
        private readonly string $sortBy = 'created_at',
        private readonly string $sortDir = 'desc',
        private readonly array $where = [],
    ) {
        $this->itemService = app(ItemService::class);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->itemService->getExportData(
            search: $this->search,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
            where: $this->where
        );
    }

    public function map($item): array
    {
        static $counter = 1;
        return [
            $counter++,
            $item->name,
            $item->description,
            $item->is_active ? 'Active' : 'Inactive',
        ];
    }


    public function headings(): array
    {
        return [
            'No',
            'Name',
            'Description',
            'Status',
        ];
    }
}
