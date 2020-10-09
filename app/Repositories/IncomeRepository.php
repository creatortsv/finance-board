<?php

namespace App\Repositories;

use App\Http\Requests\ExpenseRequest;
use App\Http\Requests\IncomeRequest;
use App\Models\Expense;
use Creatortsv\EloquentPipelinesModifier\ModifierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IncomeRepository extends RepositoryAbstract implements RepositoryInterface
{
    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this
            ->user()
            ->incomes()
            ->getQuery();
    }

    /**
     * @param IncomeRequest $request
     * @param Income $model
     * @return Income
     */
    public function save(FormRequest $request, Model $model = null): Model
    {
        $model = $model ?: new $this->modelClass;
        if ($model->exists) {
            $model = $this
                ->builder()
                ->findOrFail($model->id);
        }

        $user = $this->user();
        $data = array_merge($request->validated(), ['user_id' => $user->id]);

        DB::transaction(function () use ($data, &$model): void {
            $model->fill(Arr::except($data, 'labels'));
            $model->save();

            $pivot = [];
            foreach ($data['labels'] ?? [] as $id) {
                $pivot[$id] = ['item_model' => get_class($model)];
            }

            $model->labels()->sync($pivot);
        });

        return $model;
    }
}