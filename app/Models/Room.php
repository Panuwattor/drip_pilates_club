<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['name'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
