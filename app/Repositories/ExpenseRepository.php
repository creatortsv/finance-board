<?php

namespace App\Repositories;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Creatortsv\EloquentPipelinesModifier\ModifierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExpenseRepository extends RepositoryAbstract implements RepositoryInterface
{
    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this
            ->user()
            ->expenses()
            ->getQuery();
    }

    /**
     * @param ExpenseRequest $request
     * @param Expense $model
     * @return Expense
     */
    public function save(FormRequest $request, Model $model = null): Model
    {
        $model = $model ?: new $this->modelClass;
        if ($model->exists) {
            $model = ModifierFactory::modifyTo($this
                ->builder())
                ->findOrFail($model->id);
        }

        $user = $this->user();
        $data = array_merge($request->validated(), ['user_id' => $user->id]);

        DB::transaction(function () use ($data, &$model): void {
            $model->labels()->sync($data['labels'] ?? []);
            $model->fill(Arr::except($data, 'labels'));
            $model->save();
        });

        return $model;
    }
}