<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** แม่แบบตารางประจำสัปดาห์ */
class ClassSchedule extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
            'credit_cost' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function classType(): BelongsTo
    {
        return $this->belongsTo(ClassType::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
