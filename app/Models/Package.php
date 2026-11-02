<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasTranslatedFields;

    protected $translatable = ['name', 'description'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'price_per_class' => 'decimal:2',
            'all_class_types' => 'boolean',
            'all_branches' => 'boolean',
            'once_per_customer' => 'boolean',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function classTypes()
    {
        return $this->belongsToMany(ClassType::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function isUnlimited(): bool
    {
        return $this->type === 'unlimited';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * แพ็กที่ขายได้ที่สาขานี้ = แพ็กกลางทุกสาขา + แพ็กที่ผูกกับสาขานี้โดยเฉพาะ
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('all_branches', true)
                ->orWhereHas('branches', fn ($b) => $b->where('branches.id', $branchId));
        });
    }

    public function isAvailableAtBranch($branchId): bool
    {
        return $this->all_branches || $this->branches->contains('id', $branchId);
    }

    /**
     * โปรที่ใช้ได้กับแพ็กนี้ตอนนี้ ตัวที่ลดเยอะสุด (null = ไม่มีโปร)
     * ใช้ตอนคิดราคาแพ็กเดี่ยว เช่น หน้า checkout — หน้าที่โชว์หลายแพ็กพร้อมกัน
     * ให้ใช้ PromotionResolver โหลดโปรครั้งเดียวแทน เพื่อเลี่ยง N+1
     */
    public function activePromotion(): ?Promotion
    {
        $resolver = app(\App\Services\PromotionResolver::class);

        return $resolver->bestFor($this, $resolver->activePromotions());
    }
}
