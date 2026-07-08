<?php

namespace Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

abstract class RepositoryMockTestCase extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function emptyPaginator(int $perPage = 10): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            1
        );
    }

    protected function expectPagination(Builder $query, LengthAwarePaginator $paginator): void
    {
        $query
            ->shouldReceive('paginate')
            ->once()
            ->withAnyArgs()
            ->andReturn($paginator);
    }

    protected function expectSorting(Builder $query, string $sortBy, string $sortDir): void
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