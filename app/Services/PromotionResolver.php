<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Promotion;
use Illuminate\Support\Collection;

/**
 * รวม logic การคิดราคาหลังหักโปรโมชั่น ให้ทั้งฝั่งลูกค้าและแอดมินเรียกใช้ตัวเดียวกัน
 * จะได้คิดราคาตรงกันเสมอ
 *
 * วิธีใช้ให้ประหยัด query: โหลดโปรที่ active ครั้งเดียวต่อหน้า
 *   $promos = Promotion::activeNow()->with('packages')->get();
 * แล้วส่ง collection นี้เข้า bestFor() ต่อแพ็ก (match ในหน่วยความจำ ไม่ query ซ้ำ)
 */
class PromotionResolver
{
    /** โปรที่ active ทั้งหมด พร้อมแพ็กที่ผูกไว้ — เรียกครั้งเดียวแล้วส่งต่อ */
    public function activePromotions(): Collection
    {
        return Promotion::activeNow()->with('packages:id')->get();
    }

    /** โปรที่ลดได้มากสุดสำหรับแพ็กนี้ จาก collection ที่ส่งเข้ามา (null = ไม่มีโปร) */
    public function bestFor(Package $package, Collection $activePromotions): ?Promotion
    {
        return $activePromotions
            ->filter(fn (Promotion $promo) => $promo->appliesToPackage($package))
            ->sortByDesc(fn (Promotion $promo) => $promo->discountFor((float) $package->price))
            ->first();
    }

    /** ราคาสุทธิหลังหักโปร ไม่ต่ำกว่า 0 */
    public function priceAfterDiscount(Package $package, ?Promotion $promo): float
    {
        $price = (float) $package->price;

        if (! $promo) {
            return $price;
        }

        return max(0, round($price - $promo->discountFor($price), 2));
    }
}
