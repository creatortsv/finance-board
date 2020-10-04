<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

interface RepositoryInterface
{
    /**
     * @return Builder
     */
    public function builder(): Builder;

    /**
     * @return Collection
     */
    public function getItems(): Collection;

    /**
     * @param int $id
     * @return Model
     */
    public function find(int $id): Model;

    /**
     * @param FormRequest $request
     * @param Model $model
     * @return Model
     */
    public function save(FormRequest $request, Model $model = null): Model;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
