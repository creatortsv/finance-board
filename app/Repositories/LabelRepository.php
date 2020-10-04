<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

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
}