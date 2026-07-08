<?php

namespace Tests\Unit\Repositories;

use App\Models\Detection;
use App\Repositories\DetectionRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery;

class DetectionRepositoryMockTest extends RepositoryMockTestCase
{
    public function test_detection_repository_get_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Detection::class);
        $query = Mockery::mock(Builder::class);
        $paginator = $this->emptyPaginator();

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('with')
            ->once()
            ->with(['detectionItems.item', 'camera', 'location'])
            ->andReturnSelf();

        $this->expectSorting($query, 'created_at', 'DESC');
        $this->expectPagination($query, $paginator);

        $repository = new DetectionRepository($model);

        $result = $repository->getData();

        $this->assertSame($paginator, $result);
    }

    public function test_detection_repository_export_data_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Detection::class);
        $query = Mockery::mock(Builder::class);

        $collection = collect([
            (new Detection())->forceFill(['status' => 'complete']),
        ]);

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('with')
            ->once()
            ->with(['detectionItems.item', 'camera', 'location'])
            ->andReturnSelf();

        $this->expectSorting($query, 'created_at', 'DESC');

        $query
            ->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $repository = new DetectionRepository($model);

        $result = $repository->exportData();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($collection, $result);
    }

    public function test_detection_repository_get_count_by_status_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Detection::class);
        $query = Mockery::mock(Builder::class);

        $model
            ->shouldReceive('where')
            ->once()
            ->with('status', 'complete')
            ->andReturn($query);

        $query
            ->shouldReceive('count')
            ->once()
            ->andReturn(5);

        $repository = new DetectionRepository($model);

        $result = $repository->getCountByStatus('complete');

        $this->assertSame(5, $result);
    }

    public function test_detection_repository_get_latest_detections_uses_mock_query_builder(): void
    {
        $model = Mockery::mock(Detection::class);
        $query = Mockery::mock(Builder::class);

        $collection = collect([
            (new Detection())->forceFill(['status' => 'complete']),
            (new Detection())->forceFill(['status' => 'pending']),
        ]);

        $model
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('with')
            ->once()
            ->with(['detectionItems.item', 'camera', 'location'])
            ->andReturnSelf();

        $query
            ->shouldReceive('latest')
            ->once()
            ->andReturnSelf();

        $query
            ->shouldReceive('take')
            ->once()
            ->with(2)
            ->andReturnSelf();

        $query
            ->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $repository = new DetectionRepository($model);

        $result = $repository->getLatestDetections(2);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($collection, $result);
        $this->assertCount(2, $result);
    }
}