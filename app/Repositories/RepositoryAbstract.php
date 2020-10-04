<?php

namespace App\Repositories;

use App\Models\User;
use Creatortsv\EloquentPipelinesModifier\ModifierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

abstract class RepositoryAbstract
{
    /**
     * @return Builder
     */
    abstract public function builder(): Builder;

    /**
     * @param string $guard
     * @return User
     */
    protected function user(string $guard = 'api'): User
    {
        return request()->user($guard);
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return ModifierFactory::modifyTo($this
            ->builder())
            ->get();
    }

    /**
     * @param int $id
     * @return Model
     */
    public function find(int $id): Model
    {
        return ModifierFactory::modifyTo($this
            ->builder())
            ->findOrFail($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return ModifierFactory::modifyTo($this
            ->builder())
            ->findOrFail($id)
            ->delete();
    }

    /**
     * @param FormRequest $request
     * @param Model $model
     * @return Model
     */
    public function save(FormRequest $request, Model $model = null): Model
    {
        $data = $request->validated();
        $class = get_class($model ?: new Model);
        $model = $model ?? new $class;
        if ($model->exists) {
            $model = ModifierFactory::modifyTo($this
                ->builder())
                ->findOrFail($model->id);
        } else {
            $data = array_merge($data, ['owner_id' => $this
                ->user()
                ->id]);
        }

        $model->fill($data);
        $model->save();
        return $model;
    }
}
