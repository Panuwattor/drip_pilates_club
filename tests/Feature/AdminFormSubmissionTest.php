<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\ClassType;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Holiday;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * กรอกฟอร์มแอดมินจริงทุกช่อง ทุกฟังก์ชัน
 *
 * ต่างจาก AdminPagesTest ที่เช็คแค่ว่าหน้าเปิดได้ ชุดนี้ยิงข้อมูลเข้าไปจริง
 * แล้วเช็คว่าลงฐานข้อมูลถูกต้อง รวมถึงกรอกผิดแล้วต้องโดนปฏิเสธ
 */
class AdminFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 30]);

        $this->admin = User::where('role', 'owner')->firstOrFail();
        Storage::fake('public');
    }

    private function asAdmin()
    {
        return $this->actingAs($this->admin);
    }

    // ================================================================
    // สาขา + ห้อง
    // ================================================================

    public function test_branch_create_edit_and_delete_full_form(): void
    {
        $payload = [
            'code' => 'thonglor',
            'name_th' => 'ดริป พิลาทิส ทองหล่อ',
            'name_en' => 'DRIP Pilates Thonglor',
            'short_name_th' => 'ทองหล่อ',
            'short_name_en' => 'Thonglor',
            'address_th' => '99/1 ซอยทองหล่อ 10 แขวงคลองตันเหนือ เขตวัฒนา กรุงเทพฯ 10110',
            'address_en' => '99/1 Thonglor Soi 10, Khlong Tan Nuea, Watthana, Bangkok 10110',
            'direction_th' => 'เดินจาก BTS ทองหล่อ ทางออก 3 ประมาณ 500 เมตร',
            'direction_en' => '500m from BTS Thonglor exit 3',
            'phone' => '021234567',
            'line_id' => '@drippilates',
            'email' => 'thonglor@drippilates.test',
            'google_map_url' => 'https://maps.google.com/?q=13.7246,100.5829',
            'lat' => 13.7246,
            'lng' => 100.5829,
            'open_time' => '07:00',
            'close_time' => '21:30',
            'bank_name' => 'กสิกรไทย',
            'bank_account_name' => 'บริษัท ดริป พิลาทิส จำกัด',
            'bank_account_number' => '1234567890',
            'promptpay_id' => '0812345678',
            'sort_order' => 5,
            'is_active' => 1,
        ];

        $this->asAdmin()->post(route('admin.branches.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect();

        $branch = Branch::where('code', 'thonglor')->firstOrFail();
        $this->assertSame('ดริป พิลาทิส ทองหล่อ', $branch->name_th);
        $this->assertSame('@drippilates', $branch->line_id);
        $this->assertEqualsWithDelta(13.7246, (float) $branch->lat, 0.0001);
        $this->assertTrue((bool) $branch->is_active);

        // แก้ไขทุกช่อง
        $this->asAdmin()->put(route('admin.branches.update', $branch), array_merge($payload, [
            'name_th' => 'ดริป พิลาทิส ทองหล่อ (สาขาใหม่)',
            'close_time' => '22:00',
            'phone' => '029998888',
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $branch->refresh();
        $this->assertSame('ดริป พิลาทิส ทองหล่อ (สาขาใหม่)', $branch->name_th);
        $this->assertSame('029998888', $branch->phone);

        // ลบได้เพราะยังไม่มีรอบเรียน
        $this->asAdmin()->delete(route('admin.branches.destroy', $branch))->assertRedirect();
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    public function test_branch_rejects_bad_input(): void
    {
        $base = [
            'code' => 'x-branch',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'open_time' => '07:00',
            'close_time' => '21:00',
        ];

        // เวลาปิดก่อนเวลาเปิด
        $this->asAdmin()->post(route('admin.branches.store'), array_merge($base, [
            'open_time' => '20:00', 'close_time' => '08:00',
        ]))->assertSessionHasErrors('close_time');

        // อีเมลผิดรูปแบบ / URL ผิด / พิกัดเกินขอบเขต
        $this->asAdmin()->post(route('admin.branches.store'), array_merge($base, [
            'email' => 'ไม่ใช่อีเมล',
            'google_map_url' => 'not-a-url',
            'lat' => 999,
            'lng' => -500,
        ]))->assertSessionHasErrors(['email', 'google_map_url', 'lat', 'lng']);

        // code ซ้ำกับสาขาเดิม
        $existing = Branch::first();
        $this->asAdmin()->post(route('admin.branches.store'), array_merge($base, [
            'code' => $existing->code,
        ]))->assertSessionHasErrors('code');

        // code มีอักขระที่ alpha_dash ไม่ยอมรับ
        $this->asAdmin()->post(route('admin.branches.store'), array_merge($base, [
            'code' => 'สาขา ที่ 1!',
        ]))->assertSessionHasErrors('code');
    }

    public function test_branch_cannot_be_deleted_when_it_has_sessions(): void
    {
        $branch = ClassSession::first()->branch;

        $this->asAdmin()->delete(route('admin.branches.destroy', $branch))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_room_create_update_and_delete(): void
    {
        $branch = Branch::first();

        $this->asAdmin()->post(route('admin.branches.rooms.store', $branch), [
            'name_th' => 'ห้องรีฟอร์เมอร์ 2',
            'name_en' => 'Reformer Room 2',
            'capacity' => 8,
            'equipment_type' => 'reformer',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $room = Room::where('name_en', 'Reformer Room 2')->firstOrFail();
        $this->assertSame(8, $room->capacity);
        $this->assertTrue((bool) $room->is_active);

        $this->asAdmin()->put(route('admin.rooms.update', $room), [
            'name_th' => 'ห้องแมท',
            'name_en' => 'Mat Room',
            'capacity' => 15,
            'equipment_type' => 'mat',
            'is_active' => 0,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $room->refresh();
        $this->assertSame('Mat Room', $room->name_en);
        $this->assertSame(15, $room->capacity);
        $this->assertFalse((bool) $room->is_active);

        // ความจุเกินขอบเขต / ประเภทอุปกรณ์ไม่มีจริง
        $this->asAdmin()->post(route('admin.branches.rooms.store', $branch), [
            'name_th' => 'ห้อง', 'name_en' => 'Room',
            'capacity' => 500, 'equipment_type' => 'spaceship',
        ])->assertSessionHasErrors(['capacity', 'equipment_type']);

        $this->asAdmin()->delete(route('admin.rooms.destroy', $room))->assertRedirect();
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    // ================================================================
    // ประเภทคลาส
    // ================================================================

    public function test_class_type_full_crud(): void
    {
        $payload = [
            'code' => 'reformer-flow',
            'name_th' => 'รีฟอร์เมอร์ โฟลว์',
            'name_en' => 'Reformer Flow',
            'description_th' => 'คลาสรีฟอร์เมอร์ต่อเนื่อง เน้นการไหลลื่นของท่า เหมาะกับคนที่พอมีพื้นฐาน',
            'description_en' => 'A flowing reformer class focused on smooth transitions.',
            'suitable_for_th' => 'ผู้ที่เคยเรียนพิลาทิสมาแล้ว 5 คลาสขึ้นไป',
            'suitable_for_en' => 'Students with at least 5 prior classes',
            'level' => 'intermediate',
            'equipment_type' => 'reformer',
            'duration_min' => 55,
            'default_capacity' => 8,
            'credit_cost' => 1.5,
            'color' => '#E86A33',
            'sort_order' => 3,
            'is_active' => 1,
        ];

        $this->asAdmin()->post(route('admin.class-types.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect();

        $type = ClassType::where('code', 'reformer-flow')->firstOrFail();
        $this->assertSame(55, $type->duration_min);
        $this->assertEqualsWithDelta(1.5, (float) $type->credit_cost, 0.001);
        $this->assertSame('intermediate', $type->level);

        $this->asAdmin()->put(route('admin.class-types.update', $type), array_merge($payload, [
            'level' => 'advanced',
            'default_capacity' => 6,
            'is_active' => 0,
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $type->refresh();
        $this->assertSame('advanced', $type->level);
        $this->assertSame(6, $type->default_capacity);
        $this->assertFalse((bool) $type->is_active);

        $this->asAdmin()->delete(route('admin.class-types.destroy', $type))->assertRedirect();
        $this->assertDatabaseMissing('class_types', ['id' => $type->id]);
    }

    public function test_class_type_rejects_out_of_range_values(): void
    {
        $this->asAdmin()->post(route('admin.class-types.store'), [
            'code' => 'bad-type',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'level' => 'godlike',
            'equipment_type' => 'trampoline',
            'duration_min' => 5,      // ต่ำกว่า 15
            'default_capacity' => 0,  // ต่ำกว่า 1
            'credit_cost' => 500,     // เกิน 99
        ])->assertSessionHasErrors([
            'level', 'equipment_type', 'duration_min', 'default_capacity', 'credit_cost',
        ]);
    }

    public function test_class_type_in_use_cannot_be_deleted(): void
    {
        $type = ClassSession::first()->classType;

        $this->asAdmin()->delete(route('admin.class-types.destroy', $type))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('class_types', ['id' => $type->id]);
    }

    // ================================================================
    // ครูผู้สอน
    // ================================================================

    public function test_trainer_full_form_with_avatar_upload(): void
    {
        $branches = Branch::pluck('id')->take(2)->all();

        $this->asAdmin()->post(route('admin.trainers.store'), [
            'code' => 'kru-nam',
            'name_th' => 'ณัฐธิดา สุขสวัสดิ์',
            'name_en' => 'Nattida Suksawat',
            'nickname_th' => 'ครูน้ำ',
            'nickname_en' => 'Nam',
            'bio_th' => 'สอนพิลาทิสมากว่า 8 ปี เชี่ยวชาญการฟื้นฟูหลังการบาดเจ็บ',
            'bio_en' => 'Over 8 years teaching pilates, specialising in post-injury rehab.',
            'specialties_th' => 'รีฟอร์เมอร์, ฟื้นฟูหลังคลอด',
            'specialties_en' => 'Reformer, postnatal recovery',
            'certifications_th' => 'BASI Comprehensive, Polestar Rehab',
            'certifications_en' => 'BASI Comprehensive, Polestar Rehab',
            'phone' => '0891234567',
            'email' => 'nam@drippilates.test',
            'sort_order' => 1,
            'branch_ids' => $branches,
            'avatar_file' => UploadedFile::fake()->image('kru-nam.jpg', 400, 400),
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $trainer = Trainer::where('code', 'kru-nam')->firstOrFail();
        $this->assertSame('ครูน้ำ', $trainer->nickname_th);
        $this->assertNotNull($trainer->avatar, 'ไม่ได้บันทึกรูป avatar');
        $this->assertCount(2, $trainer->branches);
        $this->assertNotEmpty($trainer->public_token, 'ครูใหม่ต้องมี token ตารางส่วนตัว');

        // เปลี่ยนสาขาให้เหลือสาขาเดียว
        $this->asAdmin()->put(route('admin.trainers.update', $trainer), [
            'code' => 'kru-nam',
            'name_th' => 'ณัฐธิดา สุขสวัสดิ์',
            'name_en' => 'Nattida Suksawat',
            'branch_ids' => [$branches[0]],
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertCount(1, $trainer->fresh()->branches);
    }

    public function test_trainer_regenerate_token_invalidates_old_link(): void
    {
        $trainer = Trainer::first();
        $old = $trainer->public_token;

        $this->asAdmin()->post(route('admin.trainers.regenerate', $trainer))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertNotSame($old, $trainer->fresh()->public_token);

        // ลิงก์เดิมต้องใช้ไม่ได้แล้ว
        $this->get(route('trainer.schedule', $old))->assertNotFound();
        $this->get(route('trainer.schedule', $trainer->fresh()->public_token))->assertOk();
    }

    public function test_trainer_rejects_bad_avatar_and_email(): void
    {
        $this->asAdmin()->post(route('admin.trainers.store'), [
            'code' => 'bad-trainer',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'email' => 'not-an-email',
            'avatar_file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors(['email', 'avatar_file']);

        // ไฟล์รูปใหญ่เกิน 2MB
        $this->asAdmin()->post(route('admin.trainers.store'), [
            'code' => 'big-avatar',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'avatar_file' => UploadedFile::fake()->image('huge.jpg')->size(4096),
        ])->assertSessionHasErrors('avatar_file');
    }

    public function test_trainer_with_upcoming_sessions_cannot_be_deleted(): void
    {
        $session = ClassSession::where('start_at', '>', now())->whereNotNull('trainer_id')->first();
        $this->assertNotNull($session, 'ต้องมีรอบเรียนที่มีครูสอน');

        $this->asAdmin()->delete(route('admin.trainers.destroy', $session->trainer))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('trainers', ['id' => $session->trainer_id]);
    }

    // ================================================================
    // แพ็กเกจ
    // ================================================================

    public function test_package_credit_pack_full_form(): void
    {
        $classTypeIds = ClassType::pluck('id')->take(2)->all();
        $branchIds = Branch::pluck('id')->take(1)->all();

        $this->asAdmin()->post(route('admin.packages.store'), [
            'code' => 'power-20',
            'name_th' => 'แพ็ก 20 คลาส',
            'name_en' => '20 Class Pack',
            'description_th' => 'คุ้มที่สุดสำหรับคนที่เรียนประจำ ใช้ได้ 6 เดือน',
            'description_en' => 'Best value for regulars. Valid 6 months.',
            'type' => 'credit_pack',
            'price' => 18000,
            'compare_at_price' => 22000,
            'price_per_class' => 900,
            'credit_amount' => 20,
            'valid_days' => 180,
            'valid_months' => 6,
            'max_per_day' => 2,
            'max_per_week' => 5,
            'max_future_bookings' => 8,
            'sort_order' => 2,
            'all_class_types' => 0,
            'class_type_ids' => $classTypeIds,
            'all_branches' => 0,
            'branch_ids' => $branchIds,
            'once_per_customer' => 0,
            'is_public' => 1,
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $package = Package::where('code', 'power-20')->firstOrFail();
        $this->assertSame(20, $package->credit_amount);
        $this->assertEqualsWithDelta(18000, (float) $package->price, 0.01);
        $this->assertCount(2, $package->classTypes, 'ไม่ได้ผูกประเภทคลาสที่เลือก');
        $this->assertCount(1, $package->branches, 'ไม่ได้ผูกสาขาที่เลือก');
        $this->assertFalse((bool) $package->all_class_types);
    }

    public function test_unlimited_package_does_not_need_credit_amount(): void
    {
        $this->asAdmin()->post(route('admin.packages.store'), [
            'code' => 'unlimited-month',
            'name_th' => 'เหมาจ่ายรายเดือน',
            'name_en' => 'Monthly Unlimited',
            'type' => 'unlimited',
            'price' => 12000,
            'valid_days' => 30,
            'max_per_day' => 1,
            'all_class_types' => 1,
            'all_branches' => 1,
            'is_public' => 1,
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $package = Package::where('code', 'unlimited-month')->firstOrFail();
        $this->assertNull($package->credit_amount, 'แพ็กเหมาจ่ายต้องไม่มีจำนวนเครดิต');
    }

    public function test_credit_pack_requires_credit_amount(): void
    {
        $this->asAdmin()->post(route('admin.packages.store'), [
            'code' => 'no-credits',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'type' => 'credit_pack',
            'price' => 1000,
            'valid_days' => 30,
            // ตั้งใจไม่ส่ง credit_amount
        ])->assertSessionHasErrors('credit_amount');

        // ราคาติดลบ / วันหมดอายุเกินขอบเขต / ประเภทไม่มีจริง
        $this->asAdmin()->post(route('admin.packages.store'), [
            'code' => 'bad-pack',
            'name_th' => 'ทดสอบ',
            'name_en' => 'Test',
            'type' => 'mystery',
            'price' => -500,
            'valid_days' => 99999,
            'credit_amount' => 0,
        ])->assertSessionHasErrors(['type', 'price', 'valid_days', 'credit_amount']);
    }

    public function test_package_sold_to_customer_cannot_be_deleted(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::where('code', 'trio-10')->firstOrFail();
        $this->givePackage($customer, $package);

        $this->asAdmin()->delete(route('admin.packages.destroy', $package))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('packages', ['id' => $package->id]);
    }

    // ================================================================
    // ประกาศ / บทความ
    // ================================================================

    public function test_announcement_full_form_with_image(): void
    {
        $branch = Branch::first();

        $this->asAdmin()->post(route('admin.announcements.store'), [
            'branch_id' => $branch->id,
            'title_th' => 'โปรโมชันเดือนตุลาคม ลด 20%',
            'title_en' => 'October Promotion — 20% Off',
            'body_th' => '<p>ซื้อแพ็ก 10 คลาสวันนี้ <strong>รับส่วนลดทันที 20%</strong></p>',
            'body_en' => '<p>Buy a 10-class pack today and <strong>get 20% off</strong>.</p>',
            'image_file' => UploadedFile::fake()->image('promo.jpg', 1200, 630),
            'link_url' => 'https://drippilates.test/packages',
            'type' => 'promo',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'sort_order' => 1,
            'is_active' => 1,
            'show_on_homepage' => 1,
            'show_on_customer' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $announcement = Announcement::where('type', 'promo')->latest('id')->firstOrFail();
        $this->assertNotNull($announcement->image);
        $this->assertStringContainsString('<strong>', $announcement->body_th);
        $this->assertSame($branch->id, $announcement->branch_id);
        $this->assertTrue($announcement->show_on_homepage);
        $this->assertTrue($announcement->show_on_customer);
    }

    public function test_announcement_display_location_can_be_selected(): void
    {
        $this->asAdmin()->post(route('admin.announcements.store'), [
            'title_th' => 'หน้าแรกเท่านั้น',
            'title_en' => 'Homepage only',
            'body_th' => '<p>SEO content</p>',
            'type' => 'info',
            'is_active' => 1,
            'show_on_homepage' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Homepage only', false);

        $customer = Customer::create([
            'code' => 'DP-TEST-DISPLAY',
            'first_name' => 'Display',
            'phone' => '0800000099',
        ]);
        $this->actingAs($customer, 'customer')
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Homepage only', false);
    }

    public function test_announcement_strips_dangerous_html(): void
    {
        $this->asAdmin()->post(route('admin.announcements.store'), [
            'title_th' => 'ทดสอบ XSS',
            'title_en' => 'XSS test',
            // attribute อันตรายบนแท็กที่ "อนุญาต" คือจุดที่หลุดง่ายที่สุด
            'body_th' => '<p onclick="steal()">ข้อความ</p><script>alert(1)</script>'
                . '<img src=x onerror="alert(2)"><a href="javascript:alert(3)">ลิงก์</a>'
                . '<h2 onmouseover="x()">หัวข้อ</h2><strong style="a" onload="y()">ตัวหนา</strong>'
                . '<a href="JaVaScRiPt&#58;alert(4)">ลิงก์หลอก</a>',
            'body_en' => '<p>ok</p><iframe src="https://evil.test"></iframe>'
                . '<a href="https://drippilates.test/promo">Real link</a>',
            'type' => 'info',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $announcement = Announcement::latest('id')->firstOrFail();
        $both = $announcement->body_th . $announcement->body_en;

        $this->assertStringNotContainsString('<script', $both);
        $this->assertStringNotContainsString('<iframe', $both);
        $this->assertStringNotContainsString('<img', $both);

        // ห้ามมี event handler หลงเหลือบนแท็กใดๆ
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+\s*=/i', $both,
            'ยังมี event handler (onclick/onerror/onload) หลุดไปถึงหน้าบทความ');

        $this->assertStringNotContainsString('javascript:', strtolower($both));

        // เนื้อใน <script> เหลือเป็นข้อความเปล่าได้ แต่ห้ามมี href ที่รันสคริปต์
        $this->assertDoesNotMatchRegularExpression('/href\s*=\s*["\']?\s*(javascript|data|vbscript)/i', $both,
            'ยังมีลิงก์ที่รันสคริปต์ได้');

        // เนื้อหาและลิงก์ที่ถูกต้องต้องไม่ถูกตัดทิ้ง
        $this->assertStringContainsString('ข้อความ', $announcement->body_th, 'เนื้อหาปกติต้องยังอยู่');
        $this->assertStringContainsString('หัวข้อ', $announcement->body_th);
        $this->assertStringContainsString('<strong>ตัวหนา</strong>', $announcement->body_th);
        $this->assertStringContainsString('https://drippilates.test/promo', $announcement->body_en,
            'ลิงก์ปกติต้องยังใช้ได้');
        $this->assertStringContainsString('rel="noopener noreferrer"', $announcement->body_en);
    }

    public function test_announcement_rejects_bad_dates_and_url(): void
    {
        $this->asAdmin()->post(route('admin.announcements.store'), [
            'title_th' => 'ทดสอบ',
            'title_en' => 'Test',
            'type' => 'info',
            'link_url' => 'javascript:alert(1)',
            'starts_at' => now()->addMonth()->toDateString(),
            'ends_at' => now()->toDateString(), // จบก่อนเริ่ม
        ])->assertSessionHasErrors(['link_url', 'ends_at']);
    }

    public function test_announcement_appears_on_public_article_page(): void
    {
        $this->asAdmin()->post(route('admin.announcements.store'), [
            'title_th' => 'บทความทดสอบการแสดงผล',
            'title_en' => 'Public article test',
            'body_th' => '<p>เนื้อหาบทความภาษาไทย</p>',
            'body_en' => '<p>English article body</p>',
            'type' => 'info',
            'is_active' => 1,
        ])->assertRedirect();

        $announcement = Announcement::latest('id')->firstOrFail();

        // ภาษาเริ่มต้นเป็น en ต้องเห็นหัวข้ออังกฤษ
        $this->get(route('articles.show', $announcement))
            ->assertOk()
            ->assertSee('Public article test', false)
            ->assertSee('English article body', false);

        // สลับเป็นไทยแล้วต้องเห็นเนื้อหาไทย
        $this->get(route('locale.set', 'th'));

        $this->get(route('articles.show', $announcement))
            ->assertOk()
            ->assertSee('บทความทดสอบการแสดงผล', false)
            ->assertSee('เนื้อหาบทความภาษาไทย', false);
    }

    // ================================================================
    // คลิปวิดีโอ
    // ================================================================

    public function test_video_create_detects_provider_from_url(): void
    {
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'youtube',
            'https://www.instagram.com/reel/Cabc123/' => 'instagram',
            'https://www.tiktok.com/@drip/video/12345' => 'tiktok',
        ];

        foreach ($cases as $url => $expected) {
            $this->asAdmin()->post(route('admin.videos.store'), [
                'title_th' => 'คลิปสอนท่าพื้นฐาน',
                'title_en' => 'Basic moves',
                'caption_th' => 'ท่าวอร์มอัพก่อนเริ่มคลาส ทำตามได้ที่บ้าน',
                'caption_en' => 'Warm-up moves you can do at home.',
                'url' => $url,
                'sort_order' => 1,
                'is_active' => 1,
            ])->assertSessionHasNoErrors()->assertRedirect();

            $video = Video::latest('id')->firstOrFail();
            $this->assertSame($expected, $video->provider, "เดา provider จาก {$url} ผิด");
        }
    }

    public function test_video_with_explicit_provider_and_thumbnail(): void
    {
        $this->asAdmin()->post(route('admin.videos.store'), [
            'title_th' => 'รีวิวจากลูกค้า',
            'title_en' => 'Customer review',
            'url' => 'https://www.facebook.com/drippilates/videos/123456',
            'provider' => 'facebook',
            'thumbnail_file' => UploadedFile::fake()->image('cover.jpg', 800, 450),
            'sort_order' => 0,
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $video = Video::latest('id')->firstOrFail();
        $this->assertSame('facebook', $video->provider);
        $this->assertNotNull($video->thumbnail);
    }

    public function test_video_requires_valid_url(): void
    {
        $this->asAdmin()->post(route('admin.videos.store'), [
            'title_th' => 'ทดสอบ',
            'url' => 'ไม่ใช่ลิงก์',
            'provider' => 'vimeo',
        ])->assertSessionHasErrors(['url', 'provider']);

        $this->asAdmin()->post(route('admin.videos.store'), [
            'title_th' => 'ทดสอบ',
            // ไม่ส่ง url เลย
        ])->assertSessionHasErrors('url');
    }

    // ================================================================
    // ลูกค้า
    // ================================================================

    public function test_customer_create_with_every_field(): void
    {
        $branch = Branch::first();

        $this->asAdmin()->post(route('admin.customers.store'), [
            'first_name' => 'สมหญิง',
            'last_name' => 'ใจดีมาก',
            'nickname' => 'หญิง',
            'phone' => '081-234-5678',
            'email' => 'somying@example.test',
            'password' => 'secret123',
            'birth_date' => '1992-05-14',
            'gender' => 'female',
            'home_branch_id' => $branch->id,
            'preferred_locale' => 'th',
            'medical_note' => 'เคยผ่าตัดหมอนรองกระดูกเมื่อปี 2562 หลีกเลี่ยงท่าก้มหลังลึก',
            'emergency_contact_name' => 'สมชาย ใจดีมาก',
            'emergency_contact_phone' => '0819998888',
            'is_pregnant' => 0,
            'status' => 'active',
            'admin_note' => 'ลูกค้าแนะนำมาจากคุณมานี',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $customer = Customer::where('email', 'somying@example.test')->firstOrFail();

        // เบอร์ต้องถูกล้างเหลือตัวเลขล้วน ไม่งั้นผูก LINE ไม่เจอ
        $this->assertSame('0812345678', $customer->phone, 'เบอร์โทรไม่ได้ถูก normalize');
        $this->assertStringStartsWith('DP-', $customer->code);
        $this->assertNotNull($customer->profile_completed_at, 'แอดมินกรอกให้แล้วต้องถือว่าข้อมูลครบ');
        $this->assertNotSame('secret123', $customer->password, 'รหัสผ่านต้องถูก hash');
        $this->assertSame('หญิง', $customer->nickname);
    }

    public function test_customer_phone_and_email_must_be_unique(): void
    {
        $existing = $this->makeCustomer(['phone' => '0855550001', 'email' => 'dup@example.test']);

        $this->asAdmin()->post(route('admin.customers.store'), [
            'first_name' => 'ซ้ำ',
            'phone' => '0855550001',
            'email' => 'dup@example.test',
            'preferred_locale' => 'th',
            'status' => 'active',
        ])->assertSessionHasErrors(['phone', 'email']);

        // แก้ไขตัวเองด้วยเบอร์เดิมต้องผ่าน (unique ต้องยกเว้น id ตัวเอง)
        $this->asAdmin()->put(route('admin.customers.update', $existing), [
            'first_name' => 'แก้ไขแล้ว',
            'phone' => '0855550001',
            'email' => 'dup@example.test',
            'preferred_locale' => 'en',
            'status' => 'active',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('แก้ไขแล้ว', $existing->fresh()->first_name);
        $this->assertSame('en', $existing->fresh()->preferred_locale);
    }

    public function test_customer_rejects_invalid_enums_and_short_password(): void
    {
        $this->asAdmin()->post(route('admin.customers.store'), [
            'first_name' => 'ทดสอบ',
            'phone' => '0866660000',
            'password' => '123',            // สั้นกว่า 6
            'gender' => 'robot',
            'preferred_locale' => 'jp',
            'status' => 'zombie',
            'birth_date' => 'ไม่ใช่วันที่',
        ])->assertSessionHasErrors(['password', 'gender', 'preferred_locale', 'status', 'birth_date']);
    }

    public function test_customer_update_keeps_password_when_left_blank(): void
    {
        $customer = $this->makeCustomer(['password' => 'original123']);
        $before = $customer->fresh()->password;

        $this->asAdmin()->put(route('admin.customers.update', $customer), [
            'first_name' => $customer->first_name,
            'phone' => $customer->phone,
            'preferred_locale' => 'th',
            'status' => 'active',
            'password' => '', // เว้นว่าง = ไม่เปลี่ยน
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame($before, $customer->fresh()->password, 'เว้นรหัสผ่านว่างแล้วไม่ควรถูกเปลี่ยน');
    }

    public function test_customer_search_and_status_filter(): void
    {
        $this->makeCustomer(['first_name' => 'กัลยา', 'nickname' => 'กิ๊ฟ', 'phone' => '0877770001']);
        $this->makeCustomer(['first_name' => 'บรรจง', 'phone' => '0877770002', 'status' => 'banned']);

        $found = $this->asAdmin()->get(route('admin.customers.index', ['q' => 'กิ๊ฟ']))
            ->assertOk()->viewData('customers');
        $this->assertCount(1, $found);
        $this->assertSame('กัลยา', $found->first()->first_name);

        // ค้นด้วยเลขท้ายเบอร์
        $byPhone = $this->asAdmin()->get(route('admin.customers.index', ['q' => '70002']))
            ->assertOk()->viewData('customers');
        $this->assertCount(1, $byPhone);

        $banned = $this->asAdmin()->get(route('admin.customers.index', ['status' => 'banned']))
            ->assertOk()->viewData('customers');
        $this->assertTrue($banned->every(fn ($c) => $c->status === 'banned'));
    }

    public function test_customer_credit_adjustment_rejects_invalid_amounts(): void
    {
        $customer = $this->makeCustomer();
        $cp = $this->givePackage($customer);

        // ศูนย์ / เกินขอบเขต / ไม่ระบุเหตุผล
        $this->asAdmin()->post(route('admin.customers.credit', $customer), [
            'customer_package_id' => $cp->id, 'amount' => 0, 'reason' => 'ทดสอบ',
        ])->assertSessionHasErrors('amount');

        $this->asAdmin()->post(route('admin.customers.credit', $customer), [
            'customer_package_id' => $cp->id, 'amount' => 500, 'reason' => 'ทดสอบ',
        ])->assertSessionHasErrors('amount');

        $this->asAdmin()->post(route('admin.customers.credit', $customer), [
            'customer_package_id' => $cp->id, 'amount' => 5,
        ])->assertSessionHasErrors('reason');

        // หักเกินจำนวนที่เหลือ
        $this->asAdmin()->post(route('admin.customers.credit', $customer), [
            'customer_package_id' => $cp->id, 'amount' => -99, 'reason' => 'หักเกิน',
        ])->assertSessionHas('error');

        $this->assertSame(10, $cp->fresh()->credit_remaining, 'เครดิตต้องไม่เปลี่ยนเมื่อทำรายการไม่ผ่าน');
    }

    public function test_admin_cannot_adjust_credit_of_another_customers_package(): void
    {
        $victim = $this->makeCustomer(['phone' => '0844440001']);
        $attacker = $this->makeCustomer(['phone' => '0844440002']);
        $victimPackage = $this->givePackage($victim);

        $this->asAdmin()->post(route('admin.customers.credit', $attacker), [
            'customer_package_id' => $victimPackage->id,
            'amount' => 50,
            'reason' => 'พยายามเติมข้ามคน',
        ])->assertNotFound();

        $this->assertSame(10, $victimPackage->fresh()->credit_remaining);
    }

    public function test_freeze_and_unfreeze_extends_expiry(): void
    {
        $customer = $this->makeCustomer();
        $cp = $this->givePackage($customer);
        $originalExpiry = $cp->expires_at->copy();

        $this->asAdmin()->post(route('admin.customers.freeze', $cp))
            ->assertRedirect()->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame('frozen', $cp->status);
        $this->assertNotNull($cp->frozen_from);

        // ฟรีซไป 7 วันแล้วปลด ต้องได้วันหมดอายุคืน 7 วัน
        $cp->update(['frozen_from' => now()->subDays(7)->toDateString()]);

        $this->asAdmin()->post(route('admin.customers.freeze', $cp))
            ->assertRedirect()->assertSessionHas('status');

        $cp->refresh();
        $this->assertSame('active', $cp->status);
        $this->assertNull($cp->frozen_from);
        $this->assertSame(7, $cp->frozen_days_used);
        $this->assertSame(
            $originalExpiry->addDays(7)->toDateString(),
            $cp->expires_at->toDateString(),
            'ปลดฟรีซแล้ววันหมดอายุต้องขยายเท่าจำนวนวันที่ฟรีซ'
        );
    }

    // ================================================================
    // คำสั่งซื้อ / การชำระเงิน
    // ================================================================

    public function test_order_created_unpaid_does_not_issue_package(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::where('code', 'trio-10')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'branch_id' => Branch::first()->id,
            'package_id' => $package->id,
            'quantity' => 2,
            'discount' => 500,
            'discount_note' => 'ส่วนลดลูกค้าแนะนำเพื่อน',
            'note' => 'ลูกค้าขอใบเสร็จในนามบริษัท',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertEqualsWithDelta($package->price * 2, (float) $order->subtotal, 0.01);
        $this->assertEqualsWithDelta($package->price * 2 - 500, (float) $order->total, 0.01);
        $this->assertSame('ส่วนลดลูกค้าแนะนำเพื่อน', $order->discount_note);
        $this->assertCount(1, $order->items);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertStringStartsWith('ORD-', $order->code);

        // ยังไม่จ่าย ต้องยังไม่มีแพ็กและเครดิต
        $this->assertSame(0, CustomerPackage::where('customer_id', $customer->id)->count());
        $this->assertEqualsWithDelta(0, $customer->fresh()->totalCredits(), 0.01);
    }

    public function test_order_marked_paid_issues_package_and_credits(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::where('code', 'trio-10')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'discount' => 0,
            'mark_paid' => 1,
            'payment_method' => 'promptpay',
            'slip_image' => UploadedFile::fake()->image('slip.jpg', 600, 900),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame('paid', $order->fresh()->status);

        $payment = $order->payments->first();
        $this->assertSame('verified', $payment->status);
        $this->assertSame('promptpay', $payment->method);
        $this->assertNotNull($payment->slip_image, 'แนบสลิปแล้วต้องบันทึกไฟล์');

        $cp = CustomerPackage::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame($package->credit_amount, $cp->credit_remaining);
        $this->assertSame('active', $cp->status);
    }

    public function test_order_discount_cannot_push_total_below_zero(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::where('code', 'trio-10')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'discount' => 999999,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertEqualsWithDelta(0, (float) Order::latest('id')->first()->total, 0.01);
    }

    public function test_order_rejects_invalid_quantity_and_missing_refs(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::first();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => 999999,
            'package_id' => 999999,
            'quantity' => 0,
            'discount' => -100,
            'payment_method' => 'bitcoin',
        ])->assertSessionHasErrors(['customer_id', 'package_id', 'quantity', 'discount', 'payment_method']);

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 50, // เกิน 10
        ])->assertSessionHasErrors('quantity');
    }

    public function test_once_per_customer_package_cannot_be_bought_twice(): void
    {
        $package = Package::where('once_per_customer', true)->first();

        if (! $package) {
            $package = Package::first();
            $package->update(['once_per_customer' => true]);
        }

        $customer = $this->makeCustomer();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'mark_paid' => 1,
            'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertSame(1, CustomerPackage::where('customer_id', $customer->id)->count());

        // ซื้อซ้ำต้องโดนปฏิเสธ
        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 1,
            'mark_paid' => 1,
            'payment_method' => 'cash',
        ])->assertSessionHas('error');

        $this->assertSame(1, CustomerPackage::where('customer_id', $customer->id)->count());
    }

    public function test_add_payment_then_verify_issues_package(): void
    {
        $customer = $this->makeCustomer();
        $package = Package::where('code', 'trio-10')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'quantity' => 1,
        ])->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.payments.add', $order), [
            'amount' => $order->total,
            'method' => 'transfer',
            'reference' => 'KBANK-20260916-0931',
            'slip_image' => UploadedFile::fake()->image('transfer.png', 500, 800),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $payment = $order->payments()->latest('id')->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertSame('KBANK-20260916-0931', $payment->reference);
        $this->assertNotNull($payment->slip_image);
        $this->assertSame(0, CustomerPackage::where('customer_id', $customer->id)->count());

        // ยืนยัน -> ออกแพ็ก
        $this->asAdmin()->post(route('admin.payments.verify', $payment))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame('verified', $payment->fresh()->status);
        $this->assertSame($this->admin->id, $payment->fresh()->verified_by);
        $this->assertSame(1, CustomerPackage::where('customer_id', $customer->id)->count());

        // ยืนยันซ้ำต้องโดนห้าม ไม่งั้นได้แพ็กสองใบ
        $this->asAdmin()->post(route('admin.payments.verify', $payment))
            ->assertSessionHas('error');

        $this->assertSame(1, CustomerPackage::where('customer_id', $customer->id)->count(),
            'ยืนยันซ้ำต้องไม่ออกแพ็กเพิ่ม');
    }

    public function test_reject_payment_requires_reason(): void
    {
        $customer = $this->makeCustomer();

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => Package::first()->id,
            'quantity' => 1,
        ])->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.payments.add', $order), [
            'amount' => $order->total, 'method' => 'transfer',
        ])->assertRedirect();

        $payment = $order->payments()->latest('id')->firstOrFail();

        $this->asAdmin()->post(route('admin.payments.reject', $payment))
            ->assertSessionHasErrors('reject_reason');

        $this->asAdmin()->post(route('admin.payments.reject', $payment), [
            'reject_reason' => 'สลิปเป็นของรายการอื่น ยอดไม่ตรงกับคำสั่งซื้อ',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $payment->refresh();
        $this->assertSame('rejected', $payment->status);
        $this->assertStringContainsString('ยอดไม่ตรง', $payment->reject_reason);
        $this->assertSame(0, CustomerPackage::where('customer_id', $customer->id)->count());
    }

    public function test_paid_order_cannot_be_cancelled_but_pending_can(): void
    {
        $customer = $this->makeCustomer();

        // คำสั่งซื้อที่ยังไม่จ่าย ยกเลิกได้
        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id, 'package_id' => Package::first()->id, 'quantity' => 1,
        ])->assertRedirect();
        $pending = Order::latest('id')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.cancel', $pending))
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('cancelled', $pending->fresh()->status);

        // คำสั่งซื้อที่จ่ายแล้ว ยกเลิกไม่ได้
        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'package_id' => Package::where('once_per_customer', false)->first()->id,
            'quantity' => 1, 'mark_paid' => 1, 'payment_method' => 'cash',
        ])->assertRedirect();
        $paid = Order::latest('id')->firstOrFail();

        $this->asAdmin()->post(route('admin.orders.cancel', $paid))
            ->assertSessionHas('error');
        $this->assertSame('paid', $paid->fresh()->status);
    }

    public function test_order_index_filters_by_status_and_search(): void
    {
        $customer = $this->makeCustomer(['first_name' => 'ออเดอร์', 'phone' => '0833330001']);

        $this->asAdmin()->post(route('admin.orders.store'), [
            'customer_id' => $customer->id, 'package_id' => Package::first()->id, 'quantity' => 1,
        ])->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $byCode = $this->asAdmin()->get(route('admin.orders.index', ['q' => $order->code]))
            ->assertOk()->viewData('orders');
        $this->assertCount(1, $byCode);

        $byCustomer = $this->asAdmin()->get(route('admin.orders.index', ['q' => 'ออเดอร์']))
            ->assertOk()->viewData('orders');
        $this->assertGreaterThanOrEqual(1, $byCustomer->count());

        $pending = $this->asAdmin()->get(route('admin.orders.index', ['status' => 'pending']))
            ->assertOk()->viewData('orders');
        $this->assertTrue($pending->every(fn ($o) => $o->status === 'pending'));

        $this->asAdmin()->get(route('admin.orders.show', $order))->assertOk();
    }

    // ================================================================
    // หน้าเคาน์เตอร์
    // ================================================================

    public function test_counter_search_and_credit_add_deduct(): void
    {
        $customer = $this->makeCustomer(['first_name' => 'เคาน์เตอร์', 'nickname' => 'เคาท์']);
        $cp = $this->givePackage($customer);

        // ค้นเจอคนเดียวต้องเปิดโปรไฟล์ให้เลย
        $response = $this->asAdmin()->get(route('admin.counter.index', ['q' => 'เคาท์']))->assertOk();
        $this->assertNotNull($response->viewData('customer'), 'ค้นเจอคนเดียวควรเปิดให้เลย');

        // เพิ่มเครดิตด้วยเหตุผลสำเร็จรูป
        $this->asAdmin()->post(route('admin.counter.add', $customer), [
            'amount' => 2,
            'preset' => 'compensate_class',
            'customer_package_id' => $cp->id,
        ])->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('status');

        $this->assertEqualsWithDelta(12, $cp->fresh()->credit_remaining, 0.01);

        // หักเครดิต
        $this->asAdmin()->post(route('admin.counter.deduct', $customer), [
            'amount' => 3,
            'preset' => 'manual_class',
        ])->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('status');

        $this->assertEqualsWithDelta(9, $cp->fresh()->credit_remaining, 0.01);

        // เหตุผล "อื่นๆ" ต้องพิมพ์หมายเหตุ
        $this->asAdmin()->post(route('admin.counter.add', $customer), [
            'amount' => 1, 'preset' => 'other',
        ])->assertSessionHasErrors('note');

        $this->asAdmin()->post(route('admin.counter.add', $customer), [
            'amount' => 1, 'preset' => 'other', 'note' => 'ชดเชยเหตุลิฟต์เสียวันที่ 12',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('credit_transactions', [
            'customer_id' => $customer->id,
            'reason_th' => 'ชดเชยเหตุลิฟต์เสียวันที่ 12',
        ]);
    }

    public function test_counter_rejects_bad_amounts_and_presets(): void
    {
        $customer = $this->makeCustomer();
        $this->givePackage($customer);

        $this->asAdmin()->post(route('admin.counter.deduct', $customer), [
            'amount' => 0, 'preset' => 'manual_class',
        ])->assertSessionHasErrors('amount');

        $this->asAdmin()->post(route('admin.counter.deduct', $customer), [
            'amount' => 999, 'preset' => 'manual_class',
        ])->assertSessionHasErrors('amount');

        $this->asAdmin()->post(route('admin.counter.add', $customer), [
            'amount' => 1, 'preset' => 'ไม่มีเหตุผลนี้',
        ])->assertSessionHasErrors('preset');
    }

    public function test_counter_cannot_add_credit_to_another_customers_package(): void
    {
        $victim = $this->makeCustomer(['phone' => '0822220001']);
        $attacker = $this->makeCustomer(['phone' => '0822220002']);
        $victimPackage = $this->givePackage($victim);

        $this->asAdmin()->post(route('admin.counter.add', $attacker), [
            'amount' => 10,
            'preset' => 'promotion',
            'customer_package_id' => $victimPackage->id,
        ])->assertSessionHas('error');

        $this->assertEqualsWithDelta(10, $victimPackage->fresh()->credit_remaining, 0.01);
    }

    public function test_counter_deduct_fails_when_customer_has_no_credits(): void
    {
        $customer = $this->makeCustomer();

        $this->asAdmin()->post(route('admin.counter.deduct', $customer), [
            'amount' => 1, 'preset' => 'manual_class',
        ])->assertSessionHas('error');
    }

    public function test_counter_walk_in_books_and_checks_in(): void
    {
        $customer = $this->makeCustomer();
        $cp = $this->givePackage($customer);
        $session = $this->todaySession();

        $this->asAdmin()->post(route('admin.counter.walkin', $customer), [
            'class_session_id' => $session->id,
        ])->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('status');

        $booking = Booking::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('attended', $booking->status, 'walk-in ต้องเช็คอินให้เลย');
        $this->assertSame('walk_in', $booking->booked_via);
        $this->assertSame(1, $session->fresh()->booked_count);
        $this->assertLessThan(10, $cp->fresh()->credit_remaining, 'ต้องหักเครดิตตาม credit_cost');
    }

    public function test_counter_walk_in_needs_confirmation_when_class_is_full(): void
    {
        $customer = $this->makeCustomer();
        $this->givePackage($customer);
        $session = $this->todaySession();
        $session->update(['booked_count' => $session->capacity]);

        // ครั้งแรกต้องเตือนว่าเต็ม
        $this->asAdmin()->post(route('admin.counter.walkin', $customer), [
            'class_session_id' => $session->id,
        ])->assertSessionHas('error');

        $this->assertSame(0, Booking::where('customer_id', $customer->id)->count());

        // ยืนยันแล้วรับเกินได้
        $this->asAdmin()->post(route('admin.counter.walkin', $customer), [
            'class_session_id' => $session->id,
            'confirm_overbook' => 1,
        ])->assertSessionHas('status');

        $this->assertSame(1, Booking::where('customer_id', $customer->id)->count());
    }

    // ================================================================
    // รอบเรียน
    // ================================================================

    public function test_session_edit_full_form(): void
    {
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();
        $newType = ClassType::where('id', '!=', $session->class_type_id)->firstOrFail();
        $trainer = Trainer::first();
        $substitute = Trainer::where('id', '!=', $trainer->id)->first();
        $newStart = now()->addDays(3)->setTime(9, 30);

        $this->asAdmin()->put(route('admin.sessions.update', $session), [
            'class_type_id' => $newType->id,
            'trainer_id' => $trainer->id,
            'substitute_trainer_id' => $substitute?->id,
            'start_at' => $newStart->format('Y-m-d\TH:i'),
            'duration_min' => 60,
            'capacity' => 12,
            'credit_cost' => 2,
            'note_th' => 'คลาสนี้ย้ายห้องไปห้อง 2 ชั่วคราว',
            'note_en' => 'Moved to Room 2 temporarily.',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $session->refresh();
        $this->assertSame($newType->id, $session->class_type_id);
        $this->assertSame(12, $session->capacity);
        $this->assertEqualsWithDelta(2, (float) $session->credit_cost, 0.01);
        $this->assertSame($newStart->format('Y-m-d H:i'), $session->start_at->format('Y-m-d H:i'));
        // end_at ต้องถูกคำนวณจาก duration
        $this->assertSame(60, (int) $session->start_at->diffInMinutes($session->end_at));
        $this->assertSame('คลาสนี้ย้ายห้องไปห้อง 2 ชั่วคราว', $session->note_th);
    }

    public function test_session_update_rejects_bad_values(): void
    {
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();

        $this->asAdmin()->put(route('admin.sessions.update', $session), [
            'class_type_id' => 999999,
            'trainer_id' => 999999,
            'start_at' => 'ไม่ใช่วันที่',
            'duration_min' => 1,
            'capacity' => 999,
            'credit_cost' => -5,
        ])->assertSessionHasErrors([
            'class_type_id', 'trainer_id', 'start_at', 'duration_min', 'capacity', 'credit_cost',
        ]);
    }

    public function test_admin_can_book_for_customer_and_cancel_session(): void
    {
        $customer = $this->makeCustomer();
        $cp = $this->givePackage($customer);
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();

        $this->asAdmin()->post(route('admin.sessions.book', $session), [
            'customer_id' => $customer->id,
        ])->assertRedirect()->assertSessionHas('status');

        $booking = Booking::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('admin', $booking->booked_via);

        $afterBooking = $cp->fresh()->credit_remaining;
        $this->assertLessThan(10, $afterBooking, 'จองแล้วต้องหักเครดิต');

        // ยกเลิกรอบเรียนต้องคืนเครดิตให้ลูกค้าทุกคน
        $this->asAdmin()->post(route('admin.sessions.cancel', $session), [
            'reason_th' => 'ครูป่วยกะทันหัน หาครูแทนไม่ทัน',
            'reason_en' => 'Trainer fell ill, no substitute available.',
        ])->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('status');

        $this->assertSame('cancelled', $session->fresh()->status);
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertEqualsWithDelta(10, $cp->fresh()->credit_remaining, 0.01, 'ยกเลิกคลาสต้องคืนเครดิตเต็ม');
    }

    public function test_session_cancel_requires_reason_in_both_languages(): void
    {
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();

        $this->asAdmin()->post(route('admin.sessions.cancel', $session), [
            'reason_th' => 'ครูป่วย',
        ])->assertSessionHasErrors('reason_en');

        $this->assertSame('scheduled', $session->fresh()->status);
    }

    public function test_admin_cannot_book_customer_without_credits(): void
    {
        $customer = $this->makeCustomer(); // ไม่มีแพ็ก
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();

        $this->asAdmin()->post(route('admin.sessions.book', $session), [
            'customer_id' => $customer->id,
        ])->assertSessionHas('error');

        $this->assertSame(0, Booking::where('customer_id', $customer->id)->count());
    }

    public function test_set_and_clear_substitute_trainer(): void
    {
        $session = ClassSession::where('start_at', '>', now())->firstOrFail();
        $substitute = Trainer::where('id', '!=', $session->trainer_id)->firstOrFail();

        $this->asAdmin()->post(route('admin.sessions.substitute', $session), [
            'substitute_trainer_id' => $substitute->id,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame($substitute->id, $session->fresh()->substitute_trainer_id);

        $this->asAdmin()->post(route('admin.sessions.substitute', $session), [
            'substitute_trainer_id' => '',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertNull($session->fresh()->substitute_trainer_id);

        // ครูที่ไม่มีจริง
        $this->asAdmin()->post(route('admin.sessions.substitute', $session), [
            'substitute_trainer_id' => 999999,
        ])->assertSessionHasErrors('substitute_trainer_id');
    }

    public function test_session_index_day_and_week_views(): void
    {
        $branch = Branch::first();

        $day = $this->asAdmin()->get(route('admin.sessions.index', [
            'branch' => $branch->id, 'date' => now()->toDateString(), 'view' => 'day',
        ]))->assertOk();
        $this->assertSame('day', $day->viewData('view'));

        $week = $this->asAdmin()->get(route('admin.sessions.index', [
            'branch' => $branch->id, 'date' => now()->toDateString(), 'view' => 'week',
        ]))->assertOk();
        $this->assertSame('week', $week->viewData('view'));
        $this->assertGreaterThanOrEqual(
            $day->viewData('flatSessions')->count(),
            $week->viewData('flatSessions')->count(),
            'มุมมองสัปดาห์ต้องมีรอบเรียนไม่น้อยกว่ามุมมองวัน'
        );
    }

    // ================================================================
    // การจอง (เช็คอิน / ไม่มา / ยกเลิก / ย้อนสถานะ)
    // ================================================================

    public function test_booking_check_in_no_show_and_reopen(): void
    {
        $customer = $this->makeCustomer();
        $cp = $this->givePackage($customer);
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();

        $this->asAdmin()->post(route('admin.sessions.book', $session), [
            'customer_id' => $customer->id,
        ])->assertRedirect();

        $booking = Booking::where('customer_id', $customer->id)->firstOrFail();

        // เช็คอิน
        $this->asAdmin()->post(route('admin.bookings.checkin', $booking))
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('attended', $booking->fresh()->status);

        // ย้อนสถานะกลับมาแก้
        $this->asAdmin()->post(route('admin.bookings.reopen', $booking), [
            'reason' => 'กดเช็คอินผิดคน',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('confirmed', $booking->fresh()->status);

        // บันทึกว่าไม่มาเรียน
        $this->asAdmin()->post(route('admin.bookings.noshow', $booking))
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('no_show', $booking->fresh()->status);

        // ย้อนอีกครั้งแล้วยกเลิกพร้อมคืนเครดิต
        $this->asAdmin()->post(route('admin.bookings.reopen', $booking), [
            'reason' => 'ลูกค้ามาสายแต่มาเรียนจริง',
        ])->assertRedirect();

        $this->asAdmin()->post(route('admin.bookings.cancel', $booking), [
            'reason' => 'ลูกค้าขอยกเลิกทางโทรศัพท์',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertContains($booking->fresh()->status, ['cancelled', 'late_cancelled']);
        $this->assertEqualsWithDelta(10, $cp->fresh()->credit_remaining, 0.01, 'ยกเลิกล่วงหน้าต้องคืนเครดิต');
    }

    public function test_cancelled_booking_cannot_be_checked_in(): void
    {
        $customer = $this->makeCustomer();
        $this->givePackage($customer);
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();

        $this->asAdmin()->post(route('admin.sessions.book', $session), [
            'customer_id' => $customer->id,
        ])->assertRedirect();

        $booking = Booking::where('customer_id', $customer->id)->firstOrFail();

        $this->asAdmin()->post(route('admin.bookings.cancel', $booking), ['reason' => 'ยกเลิก'])
            ->assertRedirect();

        $this->asAdmin()->post(route('admin.bookings.checkin', $booking))
            ->assertSessionHas('error');

        $this->assertNotSame('attended', $booking->fresh()->status);
    }

    // ================================================================
    // ตารางประจำสัปดาห์
    // ================================================================

    public function test_schedule_create_update_and_soft_delete(): void
    {
        $branch = Branch::first();
        $room = Room::where('branch_id', $branch->id)->first();
        $classType = ClassType::first();
        $trainer = Trainer::first();

        $this->asAdmin()->post(route('admin.schedules.store'), [
            'branch_id' => $branch->id,
            'room_id' => $room?->id,
            'class_type_id' => $classType->id,
            'trainer_id' => $trainer->id,
            'day_of_week' => 3,
            'start_time' => '18:30',
            'duration_min' => 50,
            'capacity' => 10,
            'credit_cost' => 1,
            'effective_from' => now()->toDateString(),
            'effective_until' => now()->addMonths(6)->toDateString(),
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $schedule = ClassSchedule::latest('id')->firstOrFail();
        $this->assertSame(3, $schedule->day_of_week);
        $this->assertSame(10, $schedule->capacity);
        $this->assertStringStartsWith('18:30', $schedule->start_time);

        // แก้ไข
        $this->asAdmin()->put(route('admin.schedules.update', $schedule), [
            'branch_id' => $branch->id,
            'class_type_id' => $classType->id,
            'day_of_week' => 5,
            'start_time' => '07:00',
            'duration_min' => 60,
            'capacity' => 6,
            'credit_cost' => 1.5,
            'effective_from' => now()->toDateString(),
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $schedule->refresh();
        $this->assertSame(5, $schedule->day_of_week);
        $this->assertSame(6, $schedule->capacity);

        // ลบ = ปิดใช้งาน ไม่ได้ลบจริง
        $this->asAdmin()->delete(route('admin.schedules.destroy', $schedule))
            ->assertRedirect()->assertSessionHas('status');

        $schedule->refresh();
        $this->assertFalse((bool) $schedule->is_active);
        $this->assertNotNull($schedule->effective_until);
    }

    public function test_schedule_rejects_bad_day_time_and_dates(): void
    {
        $this->asAdmin()->post(route('admin.schedules.store'), [
            'branch_id' => Branch::first()->id,
            'class_type_id' => ClassType::first()->id,
            'day_of_week' => 9,           // นอกช่วง 0-6
            'start_time' => '25:99',      // เวลาไม่มีจริง
            'duration_min' => 500,        // เกิน 240
            'capacity' => 0,
            'credit_cost' => 1,
            'effective_from' => now()->toDateString(),
            'effective_until' => now()->subMonth()->toDateString(), // ก่อนวันเริ่ม
        ])->assertSessionHasErrors([
            'day_of_week', 'start_time', 'duration_min', 'capacity', 'effective_until',
        ]);
    }

    public function test_generate_sessions_button_creates_sessions(): void
    {
        $before = ClassSession::count();

        $this->asAdmin()->post(route('admin.schedules.generate'), ['days' => 60])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertGreaterThan($before, ClassSession::count(), 'กดสร้างรอบเรียนแล้วต้องมีรอบเพิ่ม');
    }

    // ================================================================
    // วันหยุด
    // ================================================================

    public function test_holiday_create_warns_about_existing_sessions(): void
    {
        $session = ClassSession::where('start_at', '>', now())->firstOrFail();
        $date = $session->start_at->toDateString();

        $response = $this->asAdmin()->post(route('admin.holidays.store'), [
            'branch_id' => $session->branch_id,
            'date' => $date,
            'reason_th' => 'วันหยุดนักขัตฤกษ์ — วันปิยมหาราช',
            'reason_en' => 'Public holiday — Chulalongkorn Day',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $holiday = Holiday::whereDate('date', $date)->firstOrFail();
        $this->assertSame('วันหยุดนักขัตฤกษ์ — วันปิยมหาราช', $holiday->reason_th);
        $this->assertTrue((bool) $holiday->is_closed_all_day);

        // ต้องเตือนว่ามีรอบเรียนค้างอยู่
        $this->assertStringContainsString('รอบเรียน', session('status'));

        $this->asAdmin()->delete(route('admin.holidays.destroy', $holiday))
            ->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    public function test_holiday_requires_both_language_reasons(): void
    {
        $this->asAdmin()->post(route('admin.holidays.store'), [
            'date' => now()->addWeek()->toDateString(),
            'reason_th' => 'ปิดปรับปรุง',
        ])->assertSessionHasErrors('reason_en');

        $this->asAdmin()->post(route('admin.holidays.store'), [
            'date' => 'ไม่ใช่วันที่',
            'reason_th' => 'ปิด', 'reason_en' => 'Closed',
        ])->assertSessionHasErrors('date');
    }

    public function test_holiday_stops_session_generation_on_that_date(): void
    {
        $branch = Branch::first();
        $date = now()->addDays(20);

        // ลบรอบเรียนวันนั้นออกก่อน แล้วตั้งเป็นวันหยุด
        ClassSession::whereDate('start_at', $date->toDateString())->delete();

        $this->asAdmin()->post(route('admin.holidays.store'), [
            'branch_id' => $branch->id,
            'date' => $date->toDateString(),
            'reason_th' => 'ปิดสตูดิโออบรมพนักงาน',
            'reason_en' => 'Closed for staff training',
        ])->assertRedirect();

        $this->asAdmin()->post(route('admin.schedules.generate'), ['days' => 60])->assertRedirect();

        $this->assertSame(0,
            ClassSession::whereDate('start_at', $date->toDateString())
                ->where('branch_id', $branch->id)->count(),
            'วันหยุดต้องไม่ถูกสร้างรอบเรียน'
        );
    }

    // ================================================================
    // ผู้ใช้งานระบบ (เจ้าของเท่านั้น)
    // ================================================================

    public function test_user_create_update_and_delete(): void
    {
        $branch = Branch::first();

        $this->asAdmin()->post(route('admin.users.store'), [
            'name' => 'พนักงานต้อนรับ อารีย์',
            'email' => 'reception.ari@drippilates.test',
            'password' => 'StrongPass123',
            'phone' => '0812223333',
            'role' => 'staff',
            'branch_id' => $branch->id,
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $user = User::where('email', 'reception.ari@drippilates.test')->firstOrFail();
        $this->assertSame('staff', $user->role);
        $this->assertSame($branch->id, $user->branch_id);
        $this->assertNotSame('StrongPass123', $user->password, 'รหัสผ่านต้องถูก hash');

        // เลื่อนเป็นผู้จัดการ ไม่เปลี่ยนรหัสผ่าน
        $before = $user->password;
        $this->asAdmin()->put(route('admin.users.update', $user), [
            'name' => 'ผู้จัดการสาขา อารีย์',
            'email' => 'reception.ari@drippilates.test',
            'password' => '',
            'phone' => '0812223333',
            'role' => 'manager',
            'branch_id' => $branch->id,
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $user->refresh();
        $this->assertSame('manager', $user->role);
        $this->assertSame($before, $user->password, 'เว้นรหัสผ่านว่างไม่ควรเปลี่ยน');

        $this->asAdmin()->delete(route('admin.users.destroy', $user))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_validation_rules(): void
    {
        $this->asAdmin()->post(route('admin.users.store'), [
            'name' => 'ทดสอบ',
            'email' => $this->admin->email, // ซ้ำ
            'password' => 'short',          // สั้นกว่า 8
            'role' => 'superadmin',         // ไม่มีบทบาทนี้
            'branch_id' => 999999,
        ])->assertSessionHasErrors(['email', 'password', 'role', 'branch_id']);

        $this->asAdmin()->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_last_owner_cannot_be_demoted_or_deleted(): void
    {
        $this->assertSame(1, User::where('role', 'owner')->count(), 'เทสต์นี้ต้องมีเจ้าของคนเดียว');

        // ถอดสิทธิ์ตัวเองไม่ได้
        $this->asAdmin()->put(route('admin.users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'staff',
        ])->assertSessionHas('error');

        $this->assertSame('owner', $this->admin->fresh()->role);

        // ลบบัญชีตัวเองไม่ได้
        $this->asAdmin()->delete(route('admin.users.destroy', $this->admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_staff_cannot_reach_user_management_endpoints(): void
    {
        $staff = User::create([
            'name' => 'พนักงาน',
            'email' => 'staff.block@test.local',
            'password' => 'password12',
            'role' => 'staff',
            'branch_id' => Branch::first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($staff)->post(route('admin.users.store'), [
            'name' => 'แอบสร้าง', 'email' => 'sneaky@test.local',
            'password' => 'password12', 'role' => 'owner',
        ])->assertForbidden();

        $this->actingAs($staff)->put(route('admin.settings.update'), [
            'settings' => ['cancel_deadline_hours' => 0],
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@test.local']);
    }

    // ================================================================
    // ตั้งค่าระบบ
    // ================================================================

    public function test_settings_update_saves_strings_ints_and_bools(): void
    {
        $this->asAdmin()->put(route('admin.settings.update'), [
            'settings' => [
                'studio_name_th' => 'ดริป พิลาทิส คลับ',
                'studio_name_en' => 'DRIP Pilates Club',
                'cancel_deadline_hours' => '12',
                'booking_open_days_ahead' => '45',
                'waitlist_max' => '5',
                'waitlist_enabled' => '1',   // ติ๊ก
                // no_show_charge_credit ไม่ส่ง = ไม่ติ๊ก
            ],
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('ดริป พิลาทิส คลับ', Setting::get('studio_name_th'));
        $this->assertSame('12', (string) Setting::get('cancel_deadline_hours'));
        $this->assertSame('45', (string) Setting::get('booking_open_days_ahead'));
        $this->assertSame('true', (string) Setting::where('key', 'waitlist_enabled')->first()->value);
        $this->assertSame('false', (string) Setting::where('key', 'no_show_charge_credit')->first()->value,
            'checkbox ที่ไม่ติ๊กต้องกลายเป็น false');
    }

    public function test_settings_ignores_non_numeric_input_for_int_fields(): void
    {
        $before = Setting::where('key', 'cancel_deadline_hours')->first()->value;

        $this->asAdmin()->put(route('admin.settings.update'), [
            'settings' => ['cancel_deadline_hours' => 'สิบสองชั่วโมง'],
        ])->assertRedirect();

        $this->assertSame($before, Setting::where('key', 'cancel_deadline_hours')->first()->value,
            'ค่าที่ไม่ใช่ตัวเลขต้องไม่ถูกบันทึกทับ');
    }

    public function test_settings_ignores_unknown_keys(): void
    {
        $this->asAdmin()->put(route('admin.settings.update'), [
            'settings' => ['ไม่มีคีย์นี้จริง' => 'xxx', 'hacked_key' => 'yyy'],
        ])->assertRedirect();

        $this->assertDatabaseMissing('settings', ['key' => 'hacked_key']);
    }

    public function test_changed_cancel_deadline_actually_affects_booking_rules(): void
    {
        $customer = $this->makeCustomer();
        $cp = $this->givePackage($customer);

        // ตั้งให้ยกเลิกฟรีได้เฉพาะก่อนคลาส 1 ชั่วโมง
        $this->asAdmin()->put(route('admin.settings.update'), [
            'settings' => ['cancel_deadline_hours' => '1'],
        ])->assertRedirect();

        // คลาสอีก 2 ชั่วโมง = ยังอยู่ในช่วงยกเลิกฟรี
        $session = ClassSession::where('start_at', '>', now()->addDay())->firstOrFail();
        $session->update([
            'start_at' => now()->addHours(2),
            'end_at' => now()->addHours(3),
        ]);

        $this->asAdmin()->post(route('admin.sessions.book', $session), [
            'customer_id' => $customer->id,
        ])->assertRedirect();

        $booking = Booking::where('customer_id', $customer->id)->firstOrFail();

        $this->asAdmin()->post(route('admin.bookings.cancel', $booking), [
            'reason' => 'ยกเลิกในช่วงที่ยังฟรี',
        ])->assertRedirect();

        $this->assertSame('cancelled', $booking->fresh()->status,
            'ยกเลิกก่อนเดดไลน์ต้องเป็น cancelled ไม่ใช่ late_cancelled');
        $this->assertEqualsWithDelta(10, $cp->fresh()->credit_remaining, 0.01);
    }

    // ================================================================
    // รายงาน + เข้าสู่ระบบ
    // ================================================================

    public function test_reports_page_accepts_date_range(): void
    {
        $this->asAdmin()->get(route('admin.reports.index', [
            'from' => now()->subMonth()->toDateString(),
            'to' => now()->toDateString(),
            'branch' => Branch::first()->id,
        ]))->assertOk();

        // ช่วงวันที่กลับด้าน / ค่ามั่ว ต้องไม่ทำหน้าพัง
        $this->asAdmin()->get(route('admin.reports.index', [
            'from' => now()->toDateString(),
            'to' => now()->subYear()->toDateString(),
        ]))->assertOk();

        $this->asAdmin()->get(route('admin.reports.index', [
            'from' => 'ไม่ใช่วันที่', 'to' => '???', 'branch' => 'abc',
        ]))->assertOk();
    }

    public function test_admin_login_flow(): void
    {
        $this->post(route('admin.login'), [
            'email' => 'admin@admin.com',
            'password' => 'ผิดแน่นอน',
        ])->assertSessionHasErrors();

        $this->assertGuest();

        $this->post(route('admin.login'), [
            'email' => 'admin@admin.com',
            'password' => 'a12345678',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($this->admin);

        $this->post(route('admin.logout'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::create([
            'name' => 'พนักงานลาออก',
            'email' => 'left@drippilates.test',
            'password' => 'password12',
            'role' => 'staff',
            'is_active' => false,
        ]);

        $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password12',
        ]);

        $this->assertFalse(
            auth()->check() && auth()->id() === $user->id,
            'พนักงานที่ปิดใช้งานต้องเข้าระบบไม่ได้'
        );
    }

    // ================================================================
    // helpers
    // ================================================================

    private function makeCustomer(array $attrs = []): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create(array_merge([
            'code' => 'FT-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => 'ทดสอบ',
            'last_name' => (string) $n,
            'phone' => '07' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'status' => 'active',
            'preferred_locale' => 'th',
        ], $attrs));
    }

    private function givePackage(Customer $customer, ?Package $package = null): CustomerPackage
    {
        $package ??= Package::where('code', 'trio-10')->firstOrFail();

        return CustomerPackage::create([
            'code' => 'CP-' . uniqid(),
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'type' => 'credit_pack',
            'purchased_at' => now(),
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays(90)->toDateString(),
            'credit_total' => 10,
            'credit_used' => 0,
            'credit_remaining' => 10,
            'status' => 'active',
        ]);
    }

    /** รอบเรียนวันนี้ที่ยังไม่จบ สำหรับทดสอบ walk-in */
    private function todaySession(): ClassSession
    {
        $session = ClassSession::where('status', 'scheduled')
            ->whereDate('start_at', now()->toDateString())
            ->where('end_at', '>', now())
            ->orderBy('start_at')
            ->first();

        if (! $session) {
            $session = ClassSession::where('start_at', '>', now())->firstOrFail();
            $session->update([
                'start_at' => now()->addMinutes(30),
                'end_at' => now()->addMinutes(80),
                'status' => 'scheduled',
            ]);
        }

        return $session;
    }
}
