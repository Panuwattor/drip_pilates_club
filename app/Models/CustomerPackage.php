<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
            'valid_days' => 'integer',
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** สถานะการจองที่ถือว่า "ใช้แพ็กไปแล้ว" ใช้กำหนดวันเริ่มนับอายุ */
    public const USING_BOOKING_STATUSES = ['confirmed', 'attended', 'no_show', 'late_cancelled'];

    /** แพ็กแบบใหม่ที่อายุเริ่มนับจากคลาสแรก (แพ็กเก่านับจากวันซื้อ valid_days เป็น null) */
    public function startsOnFirstUse(): bool
    {
        return $this->valid_days !== null;
    }

    /** เริ่มนับอายุแล้วหรือยัง แพ็กที่ยังไม่เคยใช้จะยังไม่มีวันหมดอายุ */
    public function isActivated(): bool
    {
        return $this->expires_at !== null;
    }

    /**
     * คำนวณวันเริ่ม/วันหมดอายุใหม่จากการใช้งานจริง
     *
     * วันเริ่ม = วันที่ของคลาสแรกสุดที่จองด้วยแพ็กนี้ (หรือวันที่พนักงานหักเครดิตเองหน้าร้าน)
     * ถ้ายกเลิกคลาสแรกไป วันเริ่มจะเลื่อนไปคลาสถัดไป ถ้าไม่เหลือเลยก็กลับเป็น "ยังไม่เริ่มใช้"
     * วันหมดอายุ = วันเริ่ม + valid_days + จำนวนวันที่เคยฟรีซ
     */
    public function syncActivation(): void
    {
        // แพ็กที่ปิดไปแล้ว (หมดอายุ/ยกเลิก) ไม่คำนวณใหม่ กันแอดมินย้อนแก้การจองเก่าแล้วแพ็กฟื้นขึ้นมาเอง
        if (! $this->startsOnFirstUse() || in_array($this->status, ['expired', 'cancelled'], true)) {
            return;
        }

        $firstClass = Booking::query()
            ->join('class_sessions', 'class_sessions.id', '=', 'bookings.class_session_id')
            ->where('bookings.customer_package_id', $this->id)
            ->whereIn('bookings.status', self::USING_BOOKING_STATUSES)
            ->min('class_sessions.start_at');

        // หักเครดิตเองหน้าร้าน (walk-in ที่ไม่ได้จองในระบบ) ก็นับว่าใช้แล้ว
        $firstManual = $this->creditTransactions()
            ->whereNull('booking_id')
            ->where('amount', '<', 0)
            ->where('type', 'admin_adjust')
            ->min('created_at');

        $dates = collect([$firstClass, $firstManual])
            ->filter()
            ->map(fn ($d) => Carbon::parse($d)->startOfDay());

        $start = $dates->isEmpty() ? null : $dates->min();

        $this->update([
            'starts_at' => $start?->toDateString(),
            'expires_at' => $start?->copy()
                ->addDays($this->valid_days + (int) $this->frozen_days_used)
                ->toDateString(),
        ]);
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

        if ($this->isActivated() && $this->expires_at->lt(now()->startOfDay())) {
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

    /**
     * แพ็กสีลมห้ามเอาไปจองคลาสอารีย์ เพราะขายแยกสาขากัน
     * อ่านเงื่อนไขจาก package ต้นทางแบบเดียวกับ allowsClassType()
     * แพ็กที่ all_branches = true ใช้ได้ทุกสาขาตามเดิม
     */
    public function allowsBranch(int $branchId): bool
    {
        $package = $this->package;

        if (! $package || $package->all_branches) {
            return true;
        }

        return $package->branches->contains('id', $branchId);
    }

    /**
     * ข้อความวันหมดอายุพร้อมคำนำหน้า ใช้ในทุกหน้าที่โชว์แพ็ก
     * แพ็กที่ยังไม่เริ่มใช้จะบอกว่าอายุกี่วันนับจากคลาสแรกแทน
     */
    public function expiryText(string $isoFormat = 'D MMM YYYY'): string
    {
        if ($this->isActivated()) {
            return __t('หมดอายุ', 'Expires') . ' '
                . $this->expires_at->locale(app()->getLocale())->isoFormat($isoFormat);
        }

        $days = $this->valid_days + (int) $this->frozen_days_used;

        return __t(
            "ยังไม่เริ่มใช้ · อายุ {$days} วันนับจากคลาสแรก",
            "Not started · valid {$days} days from first class"
        );
    }

    /** null = ยังไม่เริ่มใช้ อายุยังไม่เริ่มนับ */
    public function daysUntilExpiry(): ?int
    {
        if (! $this->isActivated()) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expires_at, false);
    }

    public function scopeUsable($query)
    {
        return $query->where('status', 'active')->notExpired();
    }

    /** ยังไม่หมดอายุ รวมแพ็กที่ยังไม่เริ่มใช้ (expires_at เป็น null) */
    public function scopeNotExpired($query)
    {
        return $query->where(fn ($q) => $q
            ->whereNull('customer_packages.expires_at')
            ->orWhereDate('customer_packages.expires_at', '>=', now()));
    }

    /**
     * เรียงใกล้หมดอายุก่อน แพ็กที่ยังไม่เริ่มใช้ไว้ท้ายสุด
     * จะได้ใช้แพ็กที่เปิดแล้วให้หมดก่อน ไม่ไปเปิดแพ็กใหม่ทั้งที่ใบเก่ายังเหลือ
     */
    public function scopeOrderByExpiry($query)
    {
        return $query->orderByRaw('customer_packages.expires_at IS NULL')
            ->orderBy('customer_packages.expires_at')
            ->orderBy('customer_packages.id');
    }
}
