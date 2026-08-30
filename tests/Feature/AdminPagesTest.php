<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\ClassType;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
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
