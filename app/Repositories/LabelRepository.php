<?php

namespace App\Repositories;

use App\Models\Label;
use Creatortsv\EloquentPipelinesModifier\ModifierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class LabelRepository extends RepositoryAbstract implements RepositoryInterface
{
    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this
            ->user()
            ->labels()
            ->getQuery();
    }

    /**
     * @param LabelRequest $request
     * @param Label $model
     * @return Label
     */
    public function save(FormRequest $request, Model $model = null): Model
    {
        $model = $model ?? new Label;
        if ($model->exists) {
            $model = ModifierFactory::modifyTo($this
                ->builder())
                ->findOrFail($model->id);
        } else {
            $data = array_merge($request->validated(), ['owner_id' => $this->user()->id]);
        }

        $model->fill($data);
        $model->save();
        return $model;
    }
}