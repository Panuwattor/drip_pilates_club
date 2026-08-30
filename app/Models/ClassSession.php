<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** รอบเรียนจริงที่ลูกค้าจองได้ */
class ClassSession extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['note', 'cancel_reason'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'booking_opens_at' => 'datetime',
            'booking_closes_at' => 'datetime',
            'credit_cost' => 'decimal:2',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
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

    public function substituteTrainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'substitute_trainer_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** ครูที่สอนจริง ถ้ามีคนสอนแทนให้ใช้คนนั้น */
    public function actualTrainer(): ?Trainer
    {
        return $this->substituteTrainer ?: $this->trainer;
    }

    public function spotsLeft(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function isFull(): bool
    {
        return $this->booked_count >= $this->capacity;
    }

    public function isBookable(): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $now = now();

        if ($this->booking_opens_at && $now->lt($this->booking_opens_at)) {
            return false;
        }

        if ($this->booking_closes_at && $now->gt($this->booking_closes_at)) {
            return false;
        }

        return $now->lt($this->start_at);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now());
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('start_at', $date);
    }
}
