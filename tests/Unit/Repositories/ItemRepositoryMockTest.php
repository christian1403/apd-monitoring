<?php

namespace Tests\Unit\Repositories;

use App\Models\Item;
use App\Repositories\ItemRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery;

class ItemRepositoryMockTest extends RepositoryMockTestCase
{
    public function test_item_repository_get_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Item::class);
        $query = Mockery::mock(Builder::class);
        $paginator = $this->emptyPaginator();

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $this->expectSorting($query, 'created_at', 'DESC');
        $this->expectPagination($query, $paginator);

        $repository = new ItemRepository($model);

        $result = $repository->getData();

        $this->assertSame($paginator, $result);
    }

    public function test_item_repository_export_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Item::class);
        $query = Mockery::mock(Builder::class);

        $collection = collect([
            (new Item())->forceFill(['name' => 'Safety Helmet']),
        ]);

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $this->expectSorting($query, 'created_at', 'DESC');

        $query
            ->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $repository = new ItemRepository($model);

        $result = $repository->exportData();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($collection, $result);
    }
}