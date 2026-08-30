<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPackage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'starts_at' => 'date',
            'expires_at' => 'date',
            'frozen_from' => 'date',
            'frozen_until' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function isUnlimited(): bool
    {
        return $this->type === 'unlimited';
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->starts_at->gt($today) || $this->expires_at->lt($today)) {
            return false;
        }

        return $this->isUnlimited() || $this->credit_remaining > 0;
    }

    /**
     * แพ็ก Trio ห้ามเอาไปจองคลาส Private เพราะราคาต่างกันมาก
     * เงื่อนไขอ่านจาก package ต้นทาง ถ้าแอดมินแก้ทีหลังจะมีผลกับแพ็กที่ขายไปแล้วด้วย
     */
    public function allowsClassType(int $classTypeId): bool
    {
        $package = $this->package;

        if (! $package || $package->all_class_types) {
            return true;
        }

        return $package->classTypes->contains('id', $classTypeId);
    }

    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expires_at, false);
    }

    public function scopeUsable($query)
    {
        return $query->where('status', 'active')
            ->whereDate('starts_at', '<=', now())
            ->whereDate('expires_at', '>=', now());
    }
}
