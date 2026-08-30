<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['reason'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_closed_all_day' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
