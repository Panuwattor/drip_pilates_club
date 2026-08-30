<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** ชื่อ ณ วันที่ขาย ไม่เปลี่ยนตามแม้แอดมินแก้ชื่อแพ็กทีหลัง */
    public function name(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $locale === 'en' ? $this->name_en_snapshot : $this->name_th_snapshot;
    }
}
