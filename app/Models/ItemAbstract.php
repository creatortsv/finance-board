<?php

namespace App\Models;

use Creatortsv\EloquentPipelinesModifier\WithModifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class ItemAbstract extends Model
{
    use HasFactory,
        SoftDeletes,
        WithModifier,
        WithLabels;

    /**
     * @var array
     */
    protected $fillable = [
        'comment',
        'quantity',
        'user_id',
        'activity_id',
        'date',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class)->withDefault();
    }
}
