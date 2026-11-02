<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['name'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'applies_to_all' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    /** โปรที่ใช้ได้จริงตอนนี้ = เปิดอยู่ และ now() อยู่ในช่วงเวลา */
    public function scopeActiveNow($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    public function appliesToPackage(Package $package): bool
    {
        return $this->applies_to_all || $this->packages->contains('id', $package->id);
    }

    /** จำนวนเงินที่ลดจากราคาหนึ่งหน่วย ลดไม่เกินราคาแพ็ก */
    public function discountFor(float $price): float
    {
        $amount = $this->discount_type === 'percent'
            ? round($price * (float) $this->discount_value / 100, 2)
            : (float) $this->discount_value;

        return min($amount, $price);
    }

    /** กำลังใช้งานอยู่จริงในตอนนี้หรือไม่ ไว้โชว์สถานะในหน้าแอดมิน */
    public function isRunningNow(): bool
    {
        return $this->is_active
            && $this->starts_at <= now()
            && $this->ends_at >= now();
    }
}
