<?php

namespace App\Repositories;

use App\Models\User;
use Creatortsv\EloquentPipelinesModifier\ModifierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
}
