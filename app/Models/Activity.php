<?php

namespace App\Models;

use Creatortsv\EloquentPipelinesModifier\WithModifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory,
        SoftDeletes,
        WithModifier;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'start',
        'finish',
        'owner_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'start' => 'datetime',
        'finish' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return HasMany
     */
    public function expenses(): HasMany
    {
        return $this
            ->hasMany(Expense::class)
            ->orderByDesc('date');
    }

    /**
     * @return HasMany
     */
    public function incomes(): HasMany
    {
        return $this
            ->hasMany(Income::class)
            ->orderByDesc('date');
    }
}
