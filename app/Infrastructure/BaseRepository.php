<?php

namespace App\Infrastructure;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @template T of Model
 */
abstract class BaseRepository
{
    /** @var T */
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get Next Id
     */
    public function getNextId($column = null): int
    {
        if (is_null($column)) {
            $column = $this->model->getKeyName();
        }
        if ($this->model->getKeyType() === 'string') {
            /** @var object|null */
            $row = $this->model->select(DB::raw("MAX(CAST($column AS INTEGER))"))->first();
            $latestId = (int) $row?->max;
        } else {
            $latestId = $this->model->max($column);
        }

        return $latestId ? $latestId + 1 : 1;
    }

    /**
     * Get Unique Random string id
     */
    public function getTransId(): string
    {
        $r = rand();
        $u = uniqid(getmypid().$r.(float) microtime() * 1000000, true);
        $m = session_id().$u;

        return $m;
    }

    /**
     * Save a model instance.
     *
     * @return T
     */
    public function save(array $data)
    {
        $res = $this->model->create($data);

        return $res;
    }

    /**
     * Save a model instance.
     *
     * @param  T  $data
     * @return T
     */
    public function saveModel($data)
    {
        $data->save();

        return $data;
    }

    /**
     * Bulk Insert a collection of model instance.
     *
     * @param  Collection|array  $data
     */
    public function bulkInsert($data): bool
    {
        if (is_array($data)) {
            $data = collect($data);
        }
        $data = $data->toArray();

        return $this->model->insert($data);
    }

    /**
     * Find one record by ID with optional relationship loading.
     *
     * @param  int|string  $id
     * @param  array|string|null  $with  Relationships to eager load
     * @return T|null
     */
    public function findOne($id, $with = null)
    {
        return $this->model
            ->when($with, function ($query) use ($with) {
                return $query->with($with);
            })
            ->find($id);
    }

    /**
     * Get all records with optional relationship loading.
     *
     * @param  array|string|null  $select
     * @param  array|string|null  $with  Relationships to eager load
     * @return Collection<T>
     */
    public function getAll($select = null, $with = null): Collection
    {
        return $this->model
            ->when(! is_null($select), fn ($q) => $q->select($select))
            ->when($with, function ($query) use ($with) {
                return $query->with($with);
            })
            ->get();
    }

    /**
     * Get paginated records.
     *
     * @param  int  $perPage  Number of records per page (default 10)
     * @param  array|string|null  $select  Columns to select
     * @param  array|string|null  $with  Relationships to eager load
     * @param  string  $pageName  Query string parameter name for the page
     * @return LengthAwarePaginator<T>
     */
    public function paginate(int $perPage = 10, $select = null, $with = null, string $pageName = 'page'): LengthAwarePaginator
    {
        return $this->model
            ->when(! is_null($select), fn ($q) => $q->select($select))
            ->when($with, fn ($q) => $q->with($with))
            ->paginate($perPage, ['*'], $pageName)->withQueryString();
    }

    /**
     * Get paginated records from an existing query builder.
     *
     * @param  int  $perPage
     * @param  string  $pageName
     * @return LengthAwarePaginator<T>
     */
    public function paginateQuery(Builder $query, int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator
    {
        return $query->paginate($perPage, ['*'], $pageName)->withQueryString();
    }

    /**
     * Generic method to execute a Query Builder instance.
     *
     * @return mixed
     */
    public function executeQuery(Builder $query)
    {
        return $query->get();
    }

    /**
     * Update a model by ID.
     *
     * @param  int|string  $id
     */
    public function update($id, array $data): bool
    {
        return (bool) $this->model->where($this->model->getKeyName(), $id)->update($data);
    }

    /**
     * Bulk Update a collection of model instance.
     *
     * @param  array  $ids
     * @param  Collection|array  $data
     */
    public function bulkUpdate($ids, $data): bool
    {
        if (is_array($data)) {
            $data = collect($data);
        }
        $data = $data->toArray();

        return (bool) $this->model->whereIn($this->model->getKeyName(), $ids)->update($data);
    }

    /**
     * Delete a model by ID.
     *
     * @param  int|string  $id
     */
    public function delete($id): bool
    {
        return $this->model->where($this->model->getKeyName(), $id)->delete();
    }

    /**
     * Delete by array ID.
     */
    public function bulkDelete(array $ids): bool
    {
        return $this->model->whereIn($this->model->getKeyName(), $ids)->delete();
    }

    /**
     * Find One by a column.
     *
     * @param  int|string  $id
     * @param  string|null  $column
     * @param  array|string|null  $select
     * @param  array|string|null  $with  Relationships to eager load
     * @return T|null
     */
    public function findOneByColumn($id, $column = null, $select = null, $with = null)
    {
        if (is_null($column)) {
            $column = $this->model->getKeyName();
        }

        return $this->model
            ->when(! is_null($select), fn ($q) => $q->select($select))
            ->when($with, function ($query) use ($with) {
                return $query->with($with);
            })
            ->where($column, $id)->first();
    }

    /**
     * Find records by a column.
     *
     * @param  int|string|array  $id
     * @param  string|null  $column
     * @param  array|string|null  $select
     * @param  array|string|null  $with  Relationships to eager load
     * @return Collection<T>
     */
    public function findByColumn($id, $column = null, $select = null, $with = null): Collection
    {
        if (is_null($column)) {
            $column = $this->model->getKeyName();
        }

        return $this->model
            ->when(! is_null($select), fn ($q) => $q->select($select))
            ->when($with, function ($query) use ($with) {
                return $query->with($with);
            })
            ->when(is_array($id), function ($query) use ($column, $id) {
                return $query->whereIn($column, $id);
            }, function ($query) use ($column, $id) {
                return $query->where($column, $id);
            })
            ->get();
    }

    /**
     * Is record exists.
     *
     * @param  int|string  $id
     */
    public function isExists($id, $column = null): bool
    {
        if (is_null($column)) {
            $column = $this->model->getKeyName();
        }

        return $this->model->where($column, $id)->exists();
    }

    /**
     * Return a Where Query closure to accomodate
     * search on exact value like integer or null value
     */
    protected function closureSearchExactValueQuery($searchColumn, $searchValue)
    {
        return function ($q) use ($searchColumn, $searchValue) {
            if (is_array($searchValue)) {
                $q->whereIn($searchColumn, $searchValue);
                if (in_array(null, $searchValue)) {
                    $q->orWhereNull($searchColumn);
                }
            } elseif (is_null($searchValue)) {
                $q->whereNull($searchColumn);
            } else {
                $q->where($searchColumn, $searchValue);
            }
        };
    }

    /**
     * Handle a search query transformer
     *
     * @return bool return true if using yours
     */
    protected function searchQueryTransformer(&$query, $searchColumn, $searchValue): bool
    {
        return false;
    }

    /**
     * Apply Query Search of a QueryBuilder
     *
     * @param  int|string|array  $search
     * @param  array|string  $columns
     * @return void
     */
    protected function applyQuerySearch(Builder &$query, $search, $columns = [])
    {
        if (is_array($search)) {
            $query->where(function ($q) use ($search) {
                /**
                 * @var array<string,string> $search {
                 *                           "key_column" => "Search String",
                 *                           "key_column_2" => "Another Search String",
                 *                           }
                 */
                foreach ($search as $key => $value) {
                    $normalizedSearch = preg_replace('/[^a-zA-Z0-9]/', '', $value);
                    if ($this::searchQueryTransformer($q, $key, $value) == false) {
                        $normalizedSearch = strtolower($normalizedSearch);
                        $q->whereRaw("LOWER(REPLACE(REPLACE(REPLACE($key, '-', ''), '_', ''), ' ', '')) LIKE ?", ["%$normalizedSearch%"]);
                    }
                }
            });
        } elseif (! empty($search)) {
            $normalizedSearch = preg_replace('/[^a-zA-Z0-9]/', '', $search);
            $normalizedSearch = strtolower($normalizedSearch);
            $query->where(function ($q) use ($columns, $normalizedSearch, $search) {
                $columns = (array) $columns;
                foreach ($columns as $column) {
                    if ($this::searchQueryTransformer($q, $column, $search) == false) {
                        $q->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE($column, '-', ''), '_', ''), ' ', '')) LIKE ?", ["%$normalizedSearch%"]);
                    }
                }
            });
        }
    }

    /**
     * Apply Query Search of a QueryBuilder For Related Table
     */
    protected function applyRelationQuerySearch(
        Builder &$query,
        string $searchColumn,
        $searchValue,
        bool $exact = false
    ): void {
        [$relation, $column] = explode('.', $searchColumn, 2);

        $query->whereHas($relation, function ($relationQuery) use ($column, $searchValue, $exact) {
            if ($exact) {
                return $relationQuery->where(
                    $this->closureSearchExactValueQuery($column, $searchValue)
                );
            }

            if (is_array($searchValue)) {
                return $relationQuery->whereIn($column, $searchValue);
            }

            $normalizedSearch = preg_replace('/[^a-zA-Z0-9]/', '', (string) $searchValue);
            $normalizedSearch = strtolower($normalizedSearch);

            return $relationQuery->whereRaw(
                "LOWER(REPLACE(REPLACE(REPLACE($column, '-', ''), '_', ''), ' ', '')) LIKE ?",
                ["%$normalizedSearch%"]
            );
        });
    }

    /**
     * Handle a sortkey transformation
     *
     * @return void
     */
    protected function sortKeyTransformer(&$sortKey)
    {
        //
    }

    /**
     * Apply Sorting on a Given Query
     *
     * @param  null|string|array  $sortKey
     */
    protected function applySorting(Builder &$query, $sortKey = null, ?string $order = 'ASC'): void
    {
        if (! is_null($sortKey)) {
            foreach ((array) $sortKey as $key) {
                $this::sortKeyTransformer($key);
                $query->orderBy($key, $order);
            }
        }
    }

    /**
     * Debug Purpose. To see the actual sql from query builder
     */
    protected function debugQueryAsSql($query): void
    {
        $d = str_replace(['?'], ['\'%s\''], $query->toSql());
        $d = vsprintf($d, $query->getBindings());
        dd($d);
    }
}
