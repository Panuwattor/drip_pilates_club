<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Package;
use App\Models\Promotion;
use App\Models\User;
use App\Services\PromotionResolver;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ระบบโปรโมชั่นแบบมีช่วงเวลา — ลด % หรือ บาท ทุกแพ็กหรือเฉพาะบางแพ็ก
 * ครอบคลุม: CRUD แอดมิน, logic คิดราคาใน PromotionResolver, และราคาหน้าลูกค้าซื้อเอง
 */
class PromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'owner')->firstOrFail();
    }

    private function asAdmin()
    {
        return $this->actingAs($this->admin);
    }

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'code' => 'PT-' . uniqid(),
            'first_name' => 'ทดสอบ',
            'phone' => '07' . random_int(10000000, 99999999),
            'status' => 'active',
            'preferred_locale' => 'th',
        ]);
    }

    // ================================================================
    // CRUD แอดมิน
    // ================================================================

    public function test_admin_creates_percent_promotion_for_all_packages(): void
    {
        $this->asAdmin()->post(route('admin.promotions.store'), [
            'code' => 'newyear2026',
            'name_th' => 'ลด 20% ต้อนรับปีใหม่',
            'name_en' => '20% off New Year',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'applies_to_all' => 1,
            'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $promo = Promotion::where('code', 'newyear2026')->firstOrFail();
        $this->assertSame('percent', $promo->discount_type);
        $this->assertEqualsWithDelta(20, (float) $promo->discount_value, 0.01);
        $this->assertTrue($promo->applies_to_all);
        $this->assertTrue($promo->isRunningNow());
        $this->assertCount(0, $promo->packages);
    }

    public function test_admin_creates_fixed_promotion_for_selected_packages(): void
    {
        $packageIds = Package::active()->pluck('id')->take(2)->all();

        $this->asAdmin()->post(route('admin.promotions.store'), [
            'code' => 'pick-500',
            'name_th' => 'ลด 500 เฉพาะแพ็กที่เลือก',
            'name_en' => '500 off selected',
            'discount_type' => 'fixed',
            'discount_value' => 500,
            'applies_to_all' => 0,
            'package_ids' => $packageIds,
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $promo = Promotion::where('code', 'pick-500')->firstOrFail();
        $this->assertFalse($promo->applies_to_all);
        $this->assertCount(2, $promo->packages);
    }

    public function test_promotion_rejects_bad_input(): void
    {
        // percent เกิน 100, วันสิ้นสุดก่อนวันเริ่ม, ชนิดไม่มีจริง
        $this->asAdmin()->post(route('admin.promotions.store'), [
            'code' => 'bad promo!',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'discount_type' => 'mystery',
            'discount_value' => 150,
            'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'ends_at' => now()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors(['code', 'discount_type', 'ends_at']);
    }

    public function test_percent_value_is_capped_at_100(): void
    {
        $this->asAdmin()->post(route('admin.promotions.store'), [
            'code' => 'over',
            'name_th' => 'ทดสอบ', 'name_en' => 'Test',
            'discount_type' => 'percent',
            'discount_value' => 120,
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('discount_value');
    }

    public function test_admin_can_edit_and_delete_promotion(): void
    {
        $promo = Promotion::create([
            'code' => 'edit-me',
            'name_th' => 'เดิม', 'name_en' => 'Old',
            'discount_type' => 'percent', 'discount_value' => 10,
            'applies_to_all' => true,
            'starts_at' => now(), 'ends_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->asAdmin()->put(route('admin.promotions.update', $promo), [
            'code' => 'edit-me',
            'name_th' => 'ใหม่', 'name_en' => 'New',
            'discount_type' => 'fixed', 'discount_value' => 300,
            'applies_to_all' => 1,
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $promo->refresh();
        $this->assertSame('ใหม่', $promo->name_th);
        $this->assertSame('fixed', $promo->discount_type);

        $this->asAdmin()->delete(route('admin.promotions.destroy', $promo))->assertRedirect();
        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    // ================================================================
    // logic คิดราคา (PromotionResolver)
    // ================================================================

    public function test_resolver_computes_percent_and_fixed_and_caps_at_zero(): void
    {
        $resolver = app(PromotionResolver::class);
        $package = Package::active()->first();
        $price = (float) $package->price;

        $percent = new Promotion(['discount_type' => 'percent', 'discount_value' => 25, 'applies_to_all' => true]);
        $this->assertEqualsWithDelta(round($price * 0.25, 2), $percent->discountFor($price), 0.01);
        $this->assertEqualsWithDelta(round($price * 0.75, 2), $resolver->priceAfterDiscount($package, $percent), 0.01);

        // fixed ลดเกินราคา ต้องไม่ทำให้ราคาติดลบ
        $huge = new Promotion(['discount_type' => 'fixed', 'discount_value' => $price + 99999, 'applies_to_all' => true]);
        $this->assertEqualsWithDelta($price, $huge->discountFor($price), 0.01);
        $this->assertEqualsWithDelta(0, $resolver->priceAfterDiscount($package, $huge), 0.01);
    }

    public function test_resolver_picks_biggest_discount_when_overlapping(): void
    {
        $resolver = app(PromotionResolver::class);
        $package = Package::active()->first();

        $small = Promotion::create([
            'code' => 'small', 'name_th' => 'เล็ก', 'name_en' => 'Small',
            'discount_type' => 'percent', 'discount_value' => 5, 'applies_to_all' => true,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => true,
        ]);
        $big = Promotion::create([
            'code' => 'big', 'name_th' => 'ใหญ่', 'name_en' => 'Big',
            'discount_type' => 'percent', 'discount_value' => 30, 'applies_to_all' => true,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => true,
        ]);

        $best = $resolver->bestFor($package, $resolver->activePromotions());
        $this->assertSame($big->id, $best->id, 'ต้องเลือกโปรที่ลดได้มากที่สุด');
    }

    public function test_inactive_or_out_of_window_promotions_are_ignored(): void
    {
        $resolver = app(PromotionResolver::class);
        $package = Package::active()->first();

        // ปิดใช้งาน
        Promotion::create([
            'code' => 'off', 'name_th' => 'ปิด', 'name_en' => 'Off',
            'discount_type' => 'percent', 'discount_value' => 50, 'applies_to_all' => true,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => false,
        ]);
        // หมดอายุแล้ว
        Promotion::create([
            'code' => 'expired', 'name_th' => 'หมดอายุ', 'name_en' => 'Expired',
            'discount_type' => 'percent', 'discount_value' => 50, 'applies_to_all' => true,
            'starts_at' => now()->subDays(10), 'ends_at' => now()->subDay(), 'is_active' => true,
        ]);
        // ยังไม่เริ่ม
        Promotion::create([
            'code' => 'future', 'name_th' => 'อนาคต', 'name_en' => 'Future',
            'discount_type' => 'percent', 'discount_value' => 50, 'applies_to_all' => true,
            'starts_at' => now()->addDay(), 'ends_at' => now()->addDays(10), 'is_active' => true,
        ]);

        $this->assertNull($resolver->bestFor($package, $resolver->activePromotions()));
    }

    public function test_scoped_promotion_only_applies_to_its_packages(): void
    {
        $resolver = app(PromotionResolver::class);
        $packages = Package::active()->take(2)->get();
        $target = $packages->first();
        $other = $packages->last();
        $this->assertNotSame($target->id, $other->id, 'ต้องมีแพ็กอย่างน้อย 2 ตัวในการทดสอบ');

        $promo = Promotion::create([
            'code' => 'scoped', 'name_th' => 'เฉพาะแพ็ก', 'name_en' => 'Scoped',
            'discount_type' => 'fixed', 'discount_value' => 200, 'applies_to_all' => false,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => true,
        ]);
        $promo->packages()->sync([$target->id]);

        $active = $resolver->activePromotions();
        $this->assertNotNull($resolver->bestFor($target, $active));
        $this->assertNull($resolver->bestFor($other, $active));
    }

    // ================================================================
    // ราคาหน้าลูกค้าซื้อเอง
    // ================================================================

    public function test_customer_order_applies_active_promotion_discount(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::active()->public()->where('once_per_customer', false)->firstOrFail();
        $price = (float) $package->price;

        Promotion::create([
            'code' => 'store20', 'name_th' => 'ลด 20%', 'name_en' => '20% off',
            'discount_type' => 'percent', 'discount_value' => 20, 'applies_to_all' => true,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => true,
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.purchase.store', $package), ['quantity' => 2])
            ->assertRedirect();

        $order = Order::where('customer_id', $customer->id)->latest('id')->firstOrFail();

        $this->assertEqualsWithDelta($price * 2, (float) $order->subtotal, 0.01, 'subtotal = ราคาเต็ม');
        $this->assertEqualsWithDelta(round($price * 0.20, 2) * 2, (float) $order->discount, 0.01);
        $this->assertEqualsWithDelta(round($price * 0.80, 2) * 2, (float) $order->total, 0.01);
        $this->assertSame('ลด 20%', $order->discount_note);
        // snapshot ที่ item ต้องเป็นราคาเต็ม
        $this->assertEqualsWithDelta($price, (float) $order->items->first()->unit_price, 0.01);
    }

    public function test_customer_order_has_no_discount_without_promotion(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::active()->public()->where('once_per_customer', false)->firstOrFail();

        $this->actingAs($customer, 'customer')
            ->post(route('customer.purchase.store', $package), ['quantity' => 1])
            ->assertRedirect();

        $order = Order::where('customer_id', $customer->id)->latest('id')->firstOrFail();
        $this->assertEqualsWithDelta(0, (float) $order->discount, 0.01);
        $this->assertEqualsWithDelta((float) $package->price, (float) $order->total, 0.01);
        $this->assertNull($order->discount_note);
    }

    public function test_promotion_index_and_forms_render(): void
    {
        $promo = Promotion::create([
            'code' => 'render', 'name_th' => 'ทดสอบ', 'name_en' => 'Test',
            'discount_type' => 'percent', 'discount_value' => 15, 'applies_to_all' => true,
            'starts_at' => now(), 'ends_at' => now()->addDay(), 'is_active' => true,
        ]);

        $this->asAdmin()->get(route('admin.promotions.index'))->assertOk()->assertSee('render', false);
        $this->asAdmin()->get(route('admin.promotions.create'))->assertOk();
        $this->asAdmin()->get(route('admin.promotions.edit', $promo))->assertOk();
    }
}
