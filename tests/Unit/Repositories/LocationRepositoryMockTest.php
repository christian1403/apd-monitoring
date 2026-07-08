<?php

namespace Tests\Unit\Repositories;

use App\Models\Location;
use App\Repositories\LocationRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class LocationRepositoryMockTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

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
            new Location(['name' => 'Gudang Utama']),
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

    private function emptyPaginator(int $perPage = 10): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            1
        );
    }

    private function expectPagination($query, LengthAwarePaginator $paginator): void
    {
        $query
            ->shouldReceive('paginate')
            ->once()
            ->withAnyArgs()
            ->andReturn($paginator);
    }

    private function expectSorting($query, string $sortBy, string $sortDir): void
    {
        $query
            ->shouldReceive('orderBy')
            ->once()
            ->with(
                $sortBy,
                Mockery::on(fn ($direction) => strtoupper((string) $direction) === strtoupper($sortDir))
            )
            ->andReturnSelf();
    }
}
