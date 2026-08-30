<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Trainer extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['name', 'nickname', 'bio', 'specialties', 'certifications'];

    protected $guarded = [];

    protected $hidden = ['public_token'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Trainer $trainer) {
            $trainer->public_token ??= Str::random(48);
        });
    }

    /** ให้แอดมินกดสร้าง token ใหม่ได้เวลาลิงก์หลุด */
    public function regeneratePublicToken(): string
    {
        $this->update(['public_token' => Str::random(48)]);

        return $this->public_token;
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
