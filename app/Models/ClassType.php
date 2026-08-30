<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassType extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['name', 'description', 'suitable_for'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_cost' => 'decimal:2',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
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
