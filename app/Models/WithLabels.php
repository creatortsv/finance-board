<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait WithLabels
{
    /**
     * @return BelongsToMany
     */
    public function labels(): BelongsToMany
    {
        return $this
            ->belongsToMany(Label::class, 'item_label', 'item_id')
            ->wherePivot('item_model', static::class);
    }
}
