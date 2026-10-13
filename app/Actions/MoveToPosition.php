<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class MoveToPosition
{
    /**
     * Move one model within an ordered list and renumber the list from zero.
     *
     * @template TModel of Model
     *
     * @param  Collection<int, TModel>  $ordered  Every model in the list, in its current order.
     */
    public function handle(Collection $ordered, int $id, int $position, string $column = 'position'): void
    {
        $moving = $ordered->first(fn (Model $model) => $model->getKey() === $id)
            ?? throw (new ModelNotFoundException)->setModel($ordered->isEmpty() ? Model::class : $ordered->first()::class, [$id]);

        $reordered = $ordered->reject(fn (Model $model) => $model->is($moving))->values();
        $reordered->splice(max(0, min($position, $reordered->count())), 0, [$moving]);

        DB::transaction(function () use ($reordered, $column) {
            $reordered->each(fn (Model $model, int $index) => $model->update([$column => $index]));
        });
    }

    /**
     * Move one model up (negative offset) or down (positive offset) the list.
     *
     * @template TModel of Model
     *
     * @param  Collection<int, TModel>  $ordered  Every model in the list, in its current order.
     */
    public function by(Collection $ordered, int $id, int $offset, string $column = 'position'): void
    {
        $index = $ordered->search(fn (Model $model) => $model->getKey() === $id);

        $this->handle($ordered, $id, $index === false ? 0 : $index + $offset, $column);
    }
}
