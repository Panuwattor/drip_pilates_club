<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'promoted_at' => 'datetime',
            'credit_used' => 'decimal:2',
            'credit_refunded' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function customerPackage(): BelongsTo
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BookingLog::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['confirmed', 'waitlisted'], true);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'waitlisted']);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeWaitlisted($query)
    {
        return $query->where('status', 'waitlisted');
    }
}
