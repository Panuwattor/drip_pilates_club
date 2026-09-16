<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\ClassType;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Trainer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 14]);

        $this->admin = User::where('role', 'owner')->firstOrFail();
    }

    public function test_all_admin_index_pages_load(): void
    {
        $routes = [
            'admin.dashboard',
            'admin.sessions.index',
            'admin.schedules.index',
            'admin.bookings.index',
            'admin.customers.index',
            'admin.orders.index',
            'admin.packages.index',
            'admin.branches.index',
            'admin.trainers.index',
            'admin.class-types.index',
            'admin.holidays.index',
            'admin.announcements.index',
            'admin.reports.index',
            'admin.users.index',
            'admin.settings.edit',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk("หน้า {$route} เปิดไม่ได้");
        }
    }

    public function test_admin_confirm_dialogs_use_sweetalert(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.trainers.index'));
        $response->assertOk();
        $html = $response->getContent();

        // โหลด SweetAlert และใช้ตัวช่วยกลางแทน confirm() ของเบราว์เซอร์
        $this->assertStringContainsString('sweetalert2', $html, 'ไม่ได้โหลด SweetAlert2');
        $this->assertStringContainsString('window.adminConfirm', $html, 'ไม่มีตัวช่วย adminConfirm');
        $this->assertStringContainsString('swal-admin', $html, 'ไม่มีสไตล์ swal-admin');

        // ต้องไม่มี confirm()/prompt() ที่บล็อกหน้าจอหลงเหลือ นอกจากทางถอยตอน CDN ล่ม
        preg_match_all('/(?<![\w.])(?:window\.)?(?:alert|confirm|prompt)\s*\(/', $html, $m);
        $this->assertLessThanOrEqual(
            2,
            count($m[0]),
            'ยังมี alert/confirm/prompt ของเบราว์เซอร์เหลืออยู่: '.implode(', ', $m[0])
        );
    }

    public function test_all_admin_create_pages_load(): void
    {
        $routes = [
            'admin.customers.create',
            'admin.orders.create',
            'admin.packages.create',
            'admin.branches.create',
            'admin.trainers.create',
            'admin.class-types.create',
            'admin.announcements.create',
            'admin.users.create',
            'admin.schedules.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk("หน้า {$route} เปิดไม่ได้");
        }
    }

    public function test_all_admin_edit_pages_load(): void
    {
        $pages = [
            ['admin.branches.edit', Branch::first()],
            ['admin.trainers.edit', Trainer::first()],
            ['admin.class-types.edit', ClassType::first()],
            ['admin.packages.edit', Package::first()],
            ['admin.schedules.edit', ClassSchedule::first()],
            ['admin.sessions.edit', ClassSession::first()],
            ['admin.users.edit', $this->admin],
        ];

        foreach ($pages as [$route, $model]) {
            $this->assertNotNull($model, "ไม่มีข้อมูลสำหรับ {$route}");
            $response = $this->actingAs($this->admin)->get(route($route, $model));
            $response->assertOk("หน้า {$route} เปิดไม่ได้");
        }
    }

    public function test_session_detail_page_loads(): void
    {
        $session = ClassSession::first();

        $this->actingAs($this->admin)
            ->get(route('admin.sessions.show', $session))
            ->assertOk();
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_staff_cannot_access_owner_only_pages(): void
    {
        $staff = User::create([
            'name' => 'พนักงาน',
            'email' => 'staff@test.local',
            'password' => 'password12',
            'role' => 'staff',
            'branch_id' => Branch::first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($staff)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_staff_only_sees_own_branch_sessions(): void
    {
        $branches = Branch::orderBy('id')->get();
        $this->assertGreaterThanOrEqual(2, $branches->count());

        $staff = User::create([
            'name' => 'พนักงานอารีย์',
            'email' => 'staff2@test.local',
            'password' => 'password12',
            'role' => 'staff',
            'branch_id' => $branches[0]->id,
            'is_active' => true,
        ]);

        // ขอดูสาขาอื่นที่ไม่มีสิทธิ์ ต้องถูกดึงกลับมาสาขาตัวเอง
        $response = $this->actingAs($staff)
            ->get(route('admin.sessions.index', ['branch' => $branches[1]->id]));

        $response->assertOk();
        $response->assertViewHas('branchId', $branches[0]->id);
    }

    public function test_inactive_admin_is_logged_out(): void
    {
        $this->admin->update(['is_active' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_creating_branch_requires_both_languages(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.branches.store'), [
                'code' => 'test-branch',
                'name_th' => 'สาขาทดสอบ',
                // ตั้งใจไม่ส่ง name_en
                'open_time' => '07:00',
                'close_time' => '21:00',
            ])
            ->assertSessionHasErrors('name_en');
    }

    public function test_admin_can_create_branch_with_both_languages(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.branches.store'), [
                'code' => 'test-branch',
                'name_th' => 'สาขาทดสอบ',
                'name_en' => 'Test Branch',
                'open_time' => '07:00',
                'close_time' => '21:00',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('branches', [
            'code' => 'test-branch',
            'name_th' => 'สาขาทดสอบ',
            'name_en' => 'Test Branch',
        ]);
    }

    public function test_admin_can_adjust_customer_credit_with_reason(): void
    {
        $customer = Customer::create([
            'code' => 'DP-TEST1', 'first_name' => 'ทดสอบ', 'phone' => '0812345678', 'status' => 'active',
        ]);

        $package = Package::where('code', 'trio-10')->first();
        $cp = $customer->packages()->create([
            'code' => 'CP-TEST1',
            'package_id' => $package->id,
            'type' => 'credit_pack',
            'purchased_at' => now(),
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays(90)->toDateString(),
            'credit_total' => 10, 'credit_used' => 0, 'credit_remaining' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.customers.credit', $customer), [
                'customer_package_id' => $cp->id,
                'amount' => 3,
                'reason' => 'ชดเชยคลาสที่ยกเลิก',
            ])
            ->assertRedirect();

        $this->assertSame(13, $cp->fresh()->credit_remaining);
        $this->assertDatabaseHas('credit_transactions', [
            'customer_id' => $customer->id,
            'type' => 'admin_adjust',
            'reason_th' => 'ชดเชยคลาสที่ยกเลิก',
        ]);
    }

    /** สร้างการจองคู่หนึ่งที่ "ลำดับการจอง" กับ "วันเวลาเรียน" สลับทางกัน
     *  เพื่อพิสูจน์ว่าการเรียงใช้คอลัมน์ที่เลือกจริง ไม่ใช่บังเอิญตรงกัน */
    private function seedBookingsForSorting(): array
    {
        // ชื่อสลับลำดับกับการจอง เพื่อให้เรียงตามชื่อได้ผลต่างจากเรียงตาม id ด้วย
        $zed = Customer::create([
            'code' => 'DP-SORTZ', 'first_name' => 'ศศิ', 'last_name' => 'ฮ',
            'phone' => '0800000002', 'status' => 'active',
        ]);
        $abe = Customer::create([
            'code' => 'DP-SORTA', 'first_name' => 'กมล', 'last_name' => 'ก',
            'phone' => '0800000001', 'status' => 'active',
        ]);

        $early = ClassSession::orderBy('start_at')->firstOrFail();
        $late = ClassSession::orderByDesc('start_at')->firstOrFail();
        $this->assertTrue($late->start_at->gt($early->start_at), 'ต้องมีรอบเรียนคนละเวลา');

        // จองคลาสที่เรียนทีหลังก่อน → id น้อยกว่า แต่ start_at มากกว่า
        $bookedFirst = Booking::create([
            'code' => 'BKLATE',
            'customer_id' => $zed->id,
            'class_session_id' => $late->id,
            'status' => 'confirmed',
            'credit_used' => 0,
            'booked_at' => now(),
            'booked_via' => 'admin',
        ]);

        $bookedSecond = Booking::create([
            'code' => 'BKEARLY',
            'customer_id' => $abe->id,
            'class_session_id' => $early->id,
            'status' => 'confirmed',
            'credit_used' => 0,
            'booked_at' => now(),
            'booked_via' => 'admin',
        ]);

        return [$bookedFirst, $bookedSecond];
    }

    public function test_bookings_default_sort_is_by_class_datetime(): void
    {
        [$laterClass, $earlierClass] = $this->seedBookingsForSorting();

        $bookings = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->viewData('bookings');

        $ids = $bookings->pluck('id')->all();
        $posLater = array_search($laterClass->id, $ids, true);
        $posEarlier = array_search($earlierClass->id, $ids, true);

        $this->assertNotFalse($posLater);
        $this->assertNotFalse($posEarlier);
        // ค่าเริ่มต้นคือวันเวลาเรียนล่าสุดก่อน ไม่ใช่ลำดับที่กดจอง
        $this->assertLessThan(
            $posEarlier,
            $posLater,
            'ค่าเริ่มต้นควรเรียงตามวันเวลาเรียน (ใหม่→เก่า) ไม่ใช่ตามลำดับการจอง'
        );
    }

    public function test_bookings_sort_direction_can_be_flipped(): void
    {
        $this->seedBookingsForSorting();

        $desc = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['sort' => 'date', 'dir' => 'desc']))
            ->assertOk()->viewData('bookings')->pluck('id')->all();

        $asc = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['sort' => 'date', 'dir' => 'asc']))
            ->assertOk()->viewData('bookings')->pluck('id')->all();

        $this->assertNotSame($desc, $asc, 'สลับทิศทางแล้วลำดับต้องเปลี่ยน');
        $this->assertSame(array_reverse($asc), $desc, 'asc กับ desc ควรกลับด้านกัน');
    }

    public function test_bookings_accept_every_sortable_column(): void
    {
        $this->seedBookingsForSorting();

        foreach (['date', 'customer', 'status', 'code', 'booked'] as $key) {
            foreach (['asc', 'desc'] as $dir) {
                $this->actingAs($this->admin)
                    ->get(route('admin.bookings.index', ['sort' => $key, 'dir' => $dir]))
                    ->assertOk("เรียงตาม {$key} ({$dir}) แล้วหน้าพัง");
            }
        }
    }

    /** การ join ทำให้คอลัมน์ status/code ชื่อซ้ำกันสองตาราง ถ้าไม่ระบุตารางจะพังทันที */
    public function test_bookings_sorting_works_together_with_filters(): void
    {
        $this->seedBookingsForSorting();

        foreach (['date', 'customer'] as $key) {
            $this->actingAs($this->admin)
                ->get(route('admin.bookings.index', [
                    'sort' => $key,
                    'q' => 'BK',
                    'status' => 'confirmed',
                    'branch' => Branch::first()->id,
                    'from' => now()->subMonth()->toDateString(),
                    'to' => now()->addMonths(2)->toDateString(),
                ]))
                ->assertOk("กรองพร้อมเรียงตาม {$key} แล้วหน้าพัง");
        }
    }

    public function test_bookings_table_headers_are_clickable(): void
    {
        $this->seedBookingsForSorting();

        $html = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index'))
            ->assertOk()->getContent();

        // หัวตารางต้องเป็นลิงก์กดเรียงได้ และคอลัมน์ที่เรียงอยู่ต้องมีลูกศรบอก
        $this->assertStringContainsString('sort-link', $html, 'ไม่มีลิงก์กดเรียงในหัวตาราง');
        $this->assertStringContainsString('sort=customer', $html, 'กดเรียงตามชื่อลูกค้าไม่ได้');
        $this->assertStringContainsString('sort=status', $html, 'กดเรียงตามสถานะไม่ได้');
        $this->assertMatchesRegularExpression('/bi-caret-(up|down)-fill/', $html, 'ไม่มีลูกศรบอกทิศทาง');

        // กดคอลัมน์ที่เรียงอยู่ซ้ำต้องสลับเป็น asc
        $this->assertStringContainsString('sort=date&amp;dir=asc', $html, 'กดซ้ำแล้วไม่สลับทิศทาง');
    }

    public function test_bookings_sorting_survives_filtering_and_paging(): void
    {
        $this->seedBookingsForSorting();

        $html = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['sort' => 'customer', 'dir' => 'asc', 'q' => 'BK']))
            ->assertOk()->getContent();

        // ฟอร์มค้นหาต้องพกค่าการเรียงไปด้วย ไม่งั้นกดค้นหาแล้วลำดับหาย
        $this->assertStringContainsString('name="sort" value="customer"', $html, 'ฟอร์มไม่ได้พกคีย์เรียงไปด้วย');
        $this->assertStringContainsString('name="dir" value="asc"', $html, 'ฟอร์มไม่ได้พกทิศทางไปด้วย');
    }

    public function test_bookings_reject_unknown_sort_input(): void
    {
        $this->seedBookingsForSorting();

        // ค่ามั่ว/พยายามยิง SQL เข้ามา ต้องตกกลับไปใช้ค่าเริ่มต้น ไม่ใช่พังหรือหลุดเข้า query
        foreach (['bookings.id; DROP TABLE bookings', 'customers.password', 'ไม่มีจริง', ''] as $bad) {
            $this->actingAs($this->admin)
                ->get(route('admin.bookings.index', ['sort' => $bad, 'dir' => 'sideways']))
                ->assertOk('คีย์เรียงที่ไม่รู้จักต้องตกกลับไปค่าเริ่มต้น');
        }

        $this->assertNotNull(Booking::first(), 'ตาราง bookings ต้องยังอยู่ครบ');
    }

    public function test_cannot_reduce_capacity_below_bookings(): void
    {
        $session = ClassSession::where('start_at', '>', now()->addDay())->first();
        $session->update(['booked_count' => 5, 'capacity' => 8]);

        $this->actingAs($this->admin)
            ->put(route('admin.sessions.update', $session), [
                'class_type_id' => $session->class_type_id,
                'start_at' => $session->start_at->format('Y-m-d\TH:i'),
                'duration_min' => 50,
                'capacity' => 3,
                'credit_cost' => 1,
            ])
            ->assertSessionHas('error');

        $this->assertSame(8, $session->fresh()->capacity);
    }
}
