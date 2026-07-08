<?php

namespace Tests\Unit\Repositories;

use App\Models\Camera;
use App\Repositories\CameraRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery;

class CameraRepositoryMockTest extends RepositoryMockTestCase
{
    public function test_camera_repository_get_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Camera::class);
        $query = Mockery::mock(Builder::class);
        $paginator = $this->emptyPaginator();

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('with')
            ->once()
            ->with('location')
            ->andReturnSelf();

        $this->expectSorting($query, 'created_at', 'DESC');
        $this->expectPagination($query, $paginator);

        $repository = new CameraRepository($model);

        $result = $repository->getData();

        $this->assertSame($paginator, $result);
    }

    public function test_camera_repository_export_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Camera::class);
        $query = Mockery::mock(Builder::class);

        $collection = collect([
            (new Camera())->forceFill(['name' => 'Camera 1']),
        ]);

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('with')
            ->once()
            ->with('location')
            ->andReturnSelf();

        $this->expectSorting($query, 'created_at', 'DESC');

        $query
            ->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $repository = new CameraRepository($model);

        $result = $repository->exportData();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($collection, $result);
    }
}