<?php

namespace App\Services;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Base class for every Service.
 *
 * A Service owns the business logic for one module and is the only
 * layer allowed to write to the database. Controllers call services,
 * services call models. Shared plumbing (query building, transactions,
 * default CRUD) lives here so concrete services stay declarative.
 */
abstract class BaseService
{
    /** @var class-string<Model> Concrete services must point at their model. */
    protected string $model;

    /** Relations eager-loaded by default on reads. */
    protected array $with = [];

    public function query(): Builder
    {
        return $this->model::query()->with($this->with);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate(min($perPage, 100));
    }

    public function find(string|int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->transaction(fn () => $this->model::create($data));
    }

    public function update(Model|string|int $model, array $data): Model
    {
        $model = $model instanceof Model ? $model : $this->find($model);

        return $this->transaction(function () use ($model, $data) {
            $model->update($data);

            return $model->refresh();
        });
    }

    public function delete(Model|string|int $model): bool
    {
        $model = $model instanceof Model ? $model : $this->find($model);

        return $this->transaction(fn () => (bool) $model->delete());
    }

    /**
     * Run a closure inside a database transaction.
     */
    protected function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
