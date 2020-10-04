<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

class ActivityRepository extends RepositoryAbstract implements RepositoryInterface
{
    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this
            ->user()
            ->activities()
            ->getQuery();
    }
}