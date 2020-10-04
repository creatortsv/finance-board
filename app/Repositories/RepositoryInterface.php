<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

interface RepositoryInterface
{
    /**
     * @param FormRequest $request
     * @return Model
     */
    public function save(FormRequest $request, Model $model = null): Model;
}
