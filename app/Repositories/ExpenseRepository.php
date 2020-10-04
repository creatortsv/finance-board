<?php

namespace App\Repositories;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class ExpenseRepository implements RepositoryInterface
{
    /**
     * @param ExpenseRequest $request
     * @return Expense
     */
    public function save(FormRequest $request, Model $model = null): Expense
    {
        $data = $request->validated();
        $expense = $model ?? new Expense;
        $expense->labels()->sync($data['labels']);
        $expense->fill(Arr::except($data, 'labels'));
        $expense->user()->associate($request->user('api')->id);
        $expense->save();

        return $expense;
    }
}