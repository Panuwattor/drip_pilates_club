<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Trainer;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->artisan('classes:generate', ['--days' => 14]);
    }

    private function trainerWithSession(): array
    {
        $session = ClassSession::whereNotNull('trainer_id')
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->firstOrFail();

        return [$session->trainer, $session];
    }

    public function test_trainer_schedule_opens_without_login(): void
    {
        [$trainer] = $this->trainerWithSession();

        $this->get(route('trainer.schedule', $trainer->public_token))
            ->assertOk()
            ->assertSee($trainer->name_th, false);
    }

    public function test_schedule_page_is_not_indexable(): void
    {
        [$trainer] = $this->trainerWithSession();

        $this->get(route('trainer.schedule', $trainer->public_token))
            ->assertSee('noindex', false);
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get(route('trainer.schedule', 'this-token-does-not-exist'))
            ->assertNotFound();
    }

    public function test_inactive_trainer_link_returns_404(): void
    {
        [$trainer] = $this->trainerWithSession();
        $trainer->update(['is_active' => false]);

        $this->get(route('trainer.schedule', $trainer->public_token))
            ->assertNotFound();
    }

    public function test_regenerating_token_invalidates_old_link(): void
    {
        [$trainer] = $this->trainerWithSession();
        $oldToken = $trainer->public_token;

        $trainer->regeneratePublicToken();

        $this->get(route('trainer.schedule', $oldToken))->assertNotFound();
        $this->get(route('trainer.schedule', $trainer->fresh()->public_token))->assertOk();
    }

    public function test_trainer_sees_own_session_roster_with_medical_notes(): void
    {
        [$trainer, $session] = $this->trainerWithSession();

        $customer = Customer::create([
            'code' => 'DP-00001', 'first_name' => 'มานี', 'phone' => '0811111111',
            'status' => 'active', 'medical_note' => 'ปวดหลังส่วนล่าง', 'is_pregnant' => true,
        ]);

        $package = Package::where('code', 'trio-10')->first();
        CustomerPackage::create([
            'code' => 'CP-1', 'customer_id' => $customer->id, 'package_id' => $package->id,
            'type' => 'credit_pack', 'purchased_at' => now(),
            'starts_at' => now()->toDateString(), 'expires_at' => now()->addDays(90)->toDateString(),
            'credit_total' => 10, 'credit_used' => 0, 'credit_remaining' => 10, 'status' => 'active',
        ]);

        app(BookingService::class)->book($customer, $session);

        $this->get(route('trainer.session', ['token' => $trainer->public_token, 'session' => $session->id]))
            ->assertOk()
            ->assertSee('มานี', false)
            ->assertSee('ปวดหลังส่วนล่าง', false)
            ->assertSee('กำลังตั้งครรภ์', false);
    }

    public function test_trainer_cannot_view_another_trainers_session(): void
    {
        [$trainer, $session] = $this->trainerWithSession();

        $other = Trainer::where('id', '!=', $trainer->id)->firstOrFail();

        $this->get(route('trainer.session', ['token' => $other->public_token, 'session' => $session->id]))
            ->assertForbidden();
    }

    public function test_substitute_trainer_sees_the_session(): void
    {
        [$trainer, $session] = $this->trainerWithSession();
        $sub = Trainer::where('id', '!=', $trainer->id)->firstOrFail();

        $session->update(['substitute_trainer_id' => $sub->id]);

        // ครูสอนแทนเห็นได้
        $this->get(route('trainer.session', ['token' => $sub->public_token, 'session' => $session->id]))
            ->assertOk();

        // ครูหลักที่ถูกแทนแล้วไม่เห็น
        $this->get(route('trainer.session', ['token' => $trainer->public_token, 'session' => $session->id]))
            ->assertForbidden();
    }

    public function test_schedule_shows_substitute_sessions(): void
    {
        [$trainer, $session] = $this->trainerWithSession();
        $sub = Trainer::where('id', '!=', $trainer->id)->firstOrFail();

        $session->update(['substitute_trainer_id' => $sub->id]);

        $this->get(route('trainer.schedule', [
            'token' => $sub->public_token,
            'view' => 'month',
            'date' => $session->start_at->toDateString(),
        ]))->assertOk()->assertSee($session->classType->name_th, false);
    }
}
