<?php

namespace Tests\Unit\Repositories;

use App\Models\Detection;
use App\Repositories\DetectionRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class DetectionRepositoryMockTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

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
            new Detection(['status' => 'complete']),
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
            new Detection(['status' => 'complete']),
            new Detection(['status' => 'pending']),
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