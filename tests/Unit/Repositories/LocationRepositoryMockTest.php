<?php

namespace Tests\Unit\Repositories;

use App\Models\Location;
use App\Repositories\LocationRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery;

class LocationRepositoryMockTest extends RepositoryMockTestCase
{
    public function test_location_repository_get_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Location::class);
        $query = Mockery::mock(Builder::class);
        $paginator = $this->emptyPaginator();

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $this->expectSorting($query, 'created_at', 'DESC');
        $this->expectPagination($query, $paginator);

        $repository = new LocationRepository($model);

        $result = $repository->getData();

        $this->assertSame($paginator, $result);
    }

    public function test_location_repository_export_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Location::class);
        $query = Mockery::mock(Builder::class);

        $collection = collect([
            (new Location())->forceFill(['name' => 'Gudang Utama']),
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

        $repository = new LocationRepository($model);

        $result = $repository->exportData();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($collection, $result);
    }
}