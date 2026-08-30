<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['name', 'short_name', 'address', 'direction'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function trainers()
    {
        return $this->belongsToMany(Trainer::class);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class);
    }

    public function hasBankAccount(): bool
    {
        return filled($this->bank_account_number);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
