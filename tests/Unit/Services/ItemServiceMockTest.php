<?php

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Services\FileService;
use App\Services\ItemService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ItemServiceMockTest extends TestCase
{
    private ItemRepositoryInterface $itemRepository;
    private FileService $fileService;
    private ItemService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemRepository = Mockery::mock(ItemRepositoryInterface::class);
        $this->fileService = Mockery::mock(FileService::class);

        $this->service = new ItemService(
            $this->itemRepository,
            $this->fileService
        );
    }

    public function test_get_paginated_items_using_mock_repository(): void
    {
        $items = collect([
            $this->makeItem(1, 'Safety Helmet', 'APD-001', 'Helmet proyek', true),
        ]);

        $paginator = new LengthAwarePaginator(
            $items,
            $items->count(),
            10,
            1
        );

        $this->itemRepository
            ->shouldReceive('getData')
            ->once()
            ->with('helmet', 'created_at', 'desc', 10, ['is_active' => true])
            ->andReturn($paginator);

        $result = $this->service->getPaginatedItems(
            'helmet',
            'created_at',
            'desc',
            10,
            ['is_active' => true]
        );

        $this->assertSame($paginator, $result);
        $this->assertSame(1, $result->total());
        $this->assertSame('Safety Helmet', $result->items()[0]->name);
    }

    public function test_get_export_data_using_mock_repository(): void
    {
        $items = collect([
            $this->makeItem(1, 'Safety Shoes', 'APD-002', 'Sepatu safety', true),
        ]);

        $this->itemRepository
            ->shouldReceive('exportData')
            ->once()
            ->with('shoes', 'name', 'asc', ['is_active' => true])
            ->andReturn($items);

        $result = $this->service->getExportData(
            'shoes',
            'name',
            'asc',
            ['is_active' => true]
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame('Safety Shoes', $result->first()->name);
    }

    private function makeItem(
        int $id,
        string $name,
        string $code,
        string $description,
        bool $isActive
    ): Item {
        return (new Item())->forceFill([
            'id' => $id,
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'is_active' => $isActive,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}