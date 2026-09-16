<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\ClassSession;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * ตรรกะการจองทั้งหมด ทั้งฝั่งลูกค้าและแอดมินเรียกใช้ตัวเดียวกัน
 *
 * จุดสำคัญ:
 *  - lock รอบเรียนก่อนเช็คที่ว่าง กันคนจองพร้อมกันแล้วเกินโควตา
 *  - ตัดเครดิตจากแพ็กที่ใกล้หมดอายุก่อน
 *  - unlimited ไม่ตัดเครดิต แต่เช็คโควตาต่อวัน/สัปดาห์
 *  - ยกเลิกทันเวลาคืนเครดิต ยกเลิกช้าไม่คืน
 */
class BookingService
{
    /** จองคลาส ถ้าเต็มและเปิด waitlist จะเข้าคิวให้อัตโนมัติ */
    public function book(
        Customer $customer,
        ClassSession $session,
        string $via = 'customer',
        ?int $userId = null,
    ): Booking {
        return DB::transaction(function () use ($customer, $session, $via, $userId) {
            // lock แถวนี้ไว้ก่อน คนอื่นที่จองรอบเดียวกันต้องรอ
            $session = ClassSession::lockForUpdate()->findOrFail($session->id);

            $this->assertSessionBookable($session, $via);
            $this->assertNotAlreadyBooked($customer, $session);

            $isFull = $session->booked_count >= $session->capacity;

            // walk-in ยืนอยู่หน้าเคาน์เตอร์แล้ว เข้าคิวไม่มีความหมาย
            // พนักงานเป็นคนตัดสินใจรับเกินความจุเอง (หน้า counter ถามยืนยันก่อน)
            if ($isFull && $via !== 'walk_in') {
                return $this->joinWaitlist($customer, $session, $via, $userId);
            }

            $package = $this->resolvePackage($customer, $session);
            $creditCost = $package->isUnlimited() ? 0 : (float) $session->credit_cost;

            $booking = Booking::create([
                'code' => $this->generateCode('BK'),
                'customer_id' => $customer->id,
                'class_session_id' => $session->id,
                'customer_package_id' => $package->id,
                'status' => 'confirmed',
                'credit_used' => $creditCost,
                'booked_at' => now(),
                'booked_via' => $via,
            ]);

            if ($creditCost > 0) {
                $this->deductCredit($customer, $package, $booking, $creditCost, $session);
            }

            $session->increment('booked_count');

            $this->log($booking, null, 'confirmed', $via, $userId);
            $this->notifyBookingConfirmed($customer, $session, $booking);

            return $booking->fresh();
        });
    }

    /** เข้าคิว waitlist */
    private function joinWaitlist(
        Customer $customer,
        ClassSession $session,
        string $via,
        ?int $userId,
    ): Booking {
        if (! Setting::get('waitlist_enabled', true)) {
            throw new BookingException('class_full');
        }

        $max = (int) Setting::get('waitlist_max', 10);

        if ($session->waitlist_count >= $max) {
            throw new BookingException('waitlist_full');
        }

        // ต้องมีแพ็กที่ใช้ได้ถึงจะเข้าคิวได้ แต่ยังไม่ตัดเครดิตตอนนี้
        $package = $this->resolvePackage($customer, $session);

        $position = $session->waitlist_count + 1;

        $booking = Booking::create([
            'code' => $this->generateCode('BK'),
            'customer_id' => $customer->id,
            'class_session_id' => $session->id,
            'customer_package_id' => $package->id,
            'status' => 'waitlisted',
            'credit_used' => 0,
            'booked_at' => now(),
            'booked_via' => $via,
            'waitlist_position' => $position,
        ]);

        $session->increment('waitlist_count');

        $this->log($booking, null, 'waitlisted', $via, $userId);

        return $booking->fresh();
    }

    /** ยกเลิกการจอง ระบบตัดสินเองว่าทันเวลาหรือไม่ */
    public function cancel(
        Booking $booking,
        string $by = 'customer',
        ?string $reason = null,
        ?int $userId = null,
    ): Booking {
        return DB::transaction(function () use ($booking, $by, $reason, $userId) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $booking->isActive()) {
                throw new BookingException('booking_not_active');
            }

            $session = ClassSession::lockForUpdate()->findOrFail($booking->class_session_id);
            $wasWaitlisted = $booking->status === 'waitlisted';
            $fromStatus = $booking->status;

            // คิว waitlist ยกเลิกได้เสมอ ไม่มีค่าปรับเพราะยังไม่ได้ที่นั่ง
            $isLate = ! $wasWaitlisted && $this->isLateCancel($session, $by);
            $newStatus = $isLate ? 'late_cancelled' : 'cancelled';

            // แอดมินยกเลิกให้ถือว่าทันเวลาเสมอ
            $shouldRefund = ! $isLate && $booking->credit_used > 0;

            $booking->update([
                'status' => $newStatus,
                'cancelled_at' => now(),
                'cancelled_by' => $by,
                'cancel_reason' => $reason,
                'credit_refunded' => $shouldRefund,
                'waitlist_position' => null,
            ]);

            if ($shouldRefund) {
                $this->refundCredit($booking, 'refund');
            } elseif ($isLate && $booking->credit_used > 0) {
                // บันทึกไว้เป็นหลักฐานว่าถูกตัดเพราะยกเลิกช้า
                $this->recordCreditNote($booking, 'late_cancel',
                    'ยกเลิกช้ากว่ากำหนด ไม่คืนเครดิต',
                    'Late cancellation — credit not refunded');
            }

            if ($wasWaitlisted) {
                $session->decrement('waitlist_count');
                $this->resequenceWaitlist($session);
            } else {
                $session->decrement('booked_count');
                $this->promoteFromWaitlist($session);
            }

            $this->log($booking, $fromStatus, $newStatus, $by, $userId, $reason);

            return $booking->fresh();
        });
    }

    /** เลื่อนคิวคนแรกขึ้นมาเป็นผู้จองเมื่อมีที่ว่าง */
    public function promoteFromWaitlist(ClassSession $session): ?Booking
    {
        if (! Setting::get('waitlist_auto_promote', true)) {
            return null;
        }

        $session->refresh();

        if ($session->booked_count >= $session->capacity) {
            return null;
        }

        $next = Booking::where('class_session_id', $session->id)
            ->where('status', 'waitlisted')
            ->orderBy('waitlist_position')
            ->lockForUpdate()
            ->first();

        if (! $next) {
            return null;
        }

        $customer = $next->customer;

        // แพ็กเดิมอาจหมดอายุระหว่างรอคิว ต้องหาใหม่
        try {
            $package = $this->resolvePackage($customer, $session);
        } catch (BookingException $e) {
            // ใช้แพ็กไม่ได้แล้ว ข้ามคนนี้ไปหาคนถัดไป
            $next->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => 'system',
                'cancel_reason' => 'no_usable_package',
                'waitlist_position' => null,
            ]);
            $session->decrement('waitlist_count');
            $this->log($next, 'waitlisted', 'cancelled', 'system', null, 'no_usable_package');
            $this->resequenceWaitlist($session);

            return $this->promoteFromWaitlist($session);
        }

        $creditCost = $package->isUnlimited() ? 0 : (float) $session->credit_cost;

        $next->update([
            'status' => 'confirmed',
            'customer_package_id' => $package->id,
            'credit_used' => $creditCost,
            'waitlist_position' => null,
            'promoted_at' => now(),
        ]);

        if ($creditCost > 0) {
            $this->deductCredit($customer, $package, $next, $creditCost, $session);
        }

        $session->increment('booked_count');
        $session->decrement('waitlist_count');

        $this->log($next, 'waitlisted', 'confirmed', 'system', null, 'promoted_from_waitlist');
        $this->notifyWaitlistPromoted($customer, $session, $next);

        $this->resequenceWaitlist($session);

        return $next->fresh();
    }

    /** เรียงลำดับคิวใหม่ให้ต่อเนื่อง 1,2,3 ไม่มีเลขขาด */
    private function resequenceWaitlist(ClassSession $session): void
    {
        $queue = Booking::where('class_session_id', $session->id)
            ->where('status', 'waitlisted')
            ->orderBy('waitlist_position')
            ->orderBy('id')
            ->get();

        foreach ($queue as $i => $booking) {
            $booking->updateQuietly(['waitlist_position' => $i + 1]);
        }

        $session->updateQuietly(['waitlist_count' => $queue->count()]);
    }

    /** เช็คอินหน้าร้าน */
    public function checkIn(Booking $booking, ?int $userId = null): Booking
    {
        if ($booking->status !== 'confirmed') {
            throw new BookingException('booking_not_confirmed');
        }

        $booking->update([
            'status' => 'attended',
            'checked_in_at' => now(),
            'checked_in_by' => $userId,
        ]);

        $booking->classSession->increment('attended_count');

        $this->log($booking, 'confirmed', 'attended', 'admin', $userId);

        return $booking->fresh();
    }

    /**
     * ทำเครื่องหมายไม่มาเรียน
     *
     * $by = 'system' คือระบบปิดให้เองหลังคลาสจบ (ดู bookings:close-past)
     * แอดมินย้อนแก้ทีหลังได้ด้วย reopen()
     */
    public function markNoShow(Booking $booking, ?int $userId = null, string $by = 'admin'): Booking
    {
        if ($booking->status !== 'confirmed') {
            throw new BookingException('booking_not_confirmed');
        }

        $booking->update(['status' => 'no_show']);

        if ($booking->credit_used > 0 && Setting::get('no_show_charge_credit', true)) {
            $this->recordCreditNote($booking, 'no_show',
                'ไม่มาเรียนตามนัด ไม่คืนเครดิต',
                'No-show — credit not refunded');
        }

        $this->log($booking, 'confirmed', 'no_show', $by, $userId);

        return $booking->fresh();
    }

    /**
     * แอดมินแก้ผลที่ระบบปิดไปแล้ว เช่น ลูกค้ามาเรียนจริงแต่ลืมเช็คอิน
     * ย้อนกลับไปเป็น confirmed แล้วค่อยกดเช็คอิน/ยกเลิกใหม่ตามจริง
     */
    public function reopen(Booking $booking, ?int $userId = null, ?string $reason = null): Booking
    {
        if (! in_array($booking->status, ['no_show', 'attended', 'late_cancelled', 'cancelled'], true)) {
            throw new BookingException('booking_not_reopenable');
        }

        return DB::transaction(function () use ($booking, $userId, $reason) {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            $fromStatus = $booking->status;

            // เคยยกเลิกไว้ ที่นั่งถูกคืนเข้ากองกลางไปแล้ว ต้องจองที่คืนและตัดเครดิตใหม่
            $wasCancelled = in_array($fromStatus, ['cancelled', 'late_cancelled'], true);

            if ($wasCancelled) {
                $session = ClassSession::lockForUpdate()->findOrFail($booking->class_session_id);

                if ($session->booked_count >= $session->capacity) {
                    throw new BookingException('class_full');
                }

                $session->increment('booked_count');

                // เคยคืนเครดิตตอนยกเลิก ต้องตัดกลับ ไม่งั้นลูกค้าได้เรียนฟรี
                if ($booking->credit_refunded && $booking->credit_used > 0 && $booking->customerPackage) {
                    $this->deductCredit(
                        $booking->customer,
                        $booking->customerPackage,
                        $booking,
                        (float) $booking->credit_used,
                        $session,
                    );
                }
            }

            if ($fromStatus === 'attended') {
                $booking->classSession()->decrement('attended_count');
            }

            $booking->update([
                'status' => 'confirmed',
                'checked_in_at' => null,
                'checked_in_by' => null,
                'cancelled_at' => null,
                'cancelled_by' => null,
                'cancel_reason' => null,
                'credit_refunded' => false,
            ]);

            $this->log($booking, $fromStatus, 'confirmed', 'admin', $userId, $reason ?: 'reopened_by_admin');

            return $booking->fresh();
        });
    }

    /**
     * ปิดคลาสที่จบไปแล้วแต่ไม่มีใครกดเช็คอิน
     * ตัดเครดิตไปตั้งแต่ตอนจองแล้ว ตรงนี้แค่ปิดสถานะให้ตรงความจริง
     * ไม่งั้นการจองค้างเป็น confirmed ตลอดไป กินโควตา max_future_bookings ของลูกค้าด้วย
     */
    public function closePastBookings(?int $graceMinutes = null): array
    {
        $grace = $graceMinutes ?? (int) Setting::get('auto_no_show_after_minutes', 120);
        $cutoff = now()->subMinutes($grace);

        $noShow = 0;
        $expiredWaitlist = 0;

        Booking::where('status', 'confirmed')
            ->whereHas('classSession', fn ($q) => $q
                ->where('end_at', '<', $cutoff)
                ->where('status', '!=', 'cancelled'))
            ->chunkById(200, function ($bookings) use (&$noShow) {
                foreach ($bookings as $booking) {
                    try {
                        $this->markNoShow($booking, null, 'system');
                        $noShow++;
                    } catch (BookingException $e) {
                        // สถานะเปลี่ยนไปแล้วระหว่างนี้ ข้ามไป
                    }
                }
            });

        // คิวสำรองที่ไม่เคยได้ที่นั่ง ปิดทิ้งแบบไม่ตัดเครดิต เพราะยังไม่เคยตัด
        Booking::where('status', 'waitlisted')
            ->whereHas('classSession', fn ($q) => $q->where('end_at', '<', $cutoff))
            ->chunkById(200, function ($bookings) use (&$expiredWaitlist) {
                foreach ($bookings as $booking) {
                    $booking->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancelled_by' => 'system',
                        'cancel_reason' => 'waitlist_not_promoted',
                        'waitlist_position' => null,
                    ]);

                    $this->log($booking, 'waitlisted', 'cancelled', 'system', null, 'waitlist_not_promoted');
                    $expiredWaitlist++;
                }
            });

        return ['no_show' => $noShow, 'waitlist_expired' => $expiredWaitlist];
    }

    /**
     * ปิดแพ็กที่หมดอายุแล้ว เครดิตที่เหลือถือว่าหมดสิทธิ์
     * บันทึกลง credit_transactions ไว้ด้วย เวลาลูกค้าถามว่าเครดิตหายไปไหนจะตอบได้
     */
    public function expirePackages(): int
    {
        $expired = 0;

        CustomerPackage::whereIn('status', ['active', 'frozen', 'used_up'])
            ->whereDate('expires_at', '<', now()->toDateString())
            ->chunkById(200, function ($packages) use (&$expired) {
                foreach ($packages as $package) {
                    $remaining = (float) ($package->credit_remaining ?? 0);

                    $package->update(['status' => 'expired']);

                    if ($remaining > 0) {
                        CreditTransaction::create([
                            'customer_id' => $package->customer_id,
                            'customer_package_id' => $package->id,
                            'amount' => -$remaining,
                            'balance_after' => $package->customer->totalCredits(),
                            'type' => 'expire',
                            'reason_th' => 'แพ็กหมดอายุ เครดิตคงเหลือ ' . $remaining . ' ถูกตัด',
                            'reason_en' => 'Package expired — ' . $remaining . ' credit(s) forfeited',
                        ]);
                    }

                    $expired++;
                }
            });

        return $expired;
    }

    /**
     * ส่งแจ้งเตือนก่อนคลาสเริ่ม ให้ลูกค้าที่จองยืนยันแล้ว (ลด no-show)
     *
     * ยิงเมื่อคลาสจะเริ่มภายในหน้าต่าง [ตอนนี้, ตอนนี้ + X ชม.] แต่ยังไม่เริ่มจริง
     * กันส่งซ้ำด้วยการเช็ค notification type class_reminder ของ booking เดิม
     * (คำสั่งนี้ตั้งให้รันบ่อยได้ ทุกครั้งจะส่งเฉพาะคนที่ยังไม่เคยได้เตือน)
     *
     * @return int จำนวนแจ้งเตือนที่ส่งรอบนี้
     */
    public function sendClassReminders(?int $hoursBefore = null): int
    {
        if (! Setting::get('class_reminder_enabled', true)) {
            return 0;
        }

        $hours = $hoursBefore ?? (int) Setting::get('class_reminder_hours', 12);
        $windowEnd = now()->addHours($hours);

        $sent = 0;

        Booking::where('status', 'confirmed')
            ->whereHas('classSession', fn ($q) => $q
                ->where('status', 'scheduled')
                ->where('start_at', '>', now())
                ->where('start_at', '<=', $windowEnd))
            ->with(['classSession.classType', 'classSession.branch', 'customer'])
            ->chunkById(200, function ($bookings) use (&$sent) {
                foreach ($bookings as $booking) {
                    // เคยเตือนคลาสนี้ให้ลูกค้าคนนี้แล้ว ข้าม
                    $already = Notification::where('customer_id', $booking->customer_id)
                        ->where('type', 'class_reminder')
                        ->whereJsonContains('data->booking_id', $booking->id)
                        ->exists();

                    if ($already) {
                        continue;
                    }

                    $this->notifyClassReminder($booking->customer, $booking->classSession, $booking);
                    $sent++;
                }
            });

        return $sent;
    }

    /** แอดมินยกเลิกทั้งรอบ คืนเครดิตทุกคนอัตโนมัติ */
    public function cancelSession(
        ClassSession $session,
        string $reasonTh,
        string $reasonEn,
        ?int $userId = null,
    ): int {
        return DB::transaction(function () use ($session, $reasonTh, $reasonEn, $userId) {
            $session = ClassSession::lockForUpdate()->findOrFail($session->id);

            $bookings = Booking::where('class_session_id', $session->id)
                ->whereIn('status', ['confirmed', 'waitlisted'])
                ->get();

            foreach ($bookings as $booking) {
                $fromStatus = $booking->status;

                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => 'admin',
                    'cancel_reason' => 'session_cancelled',
                    'credit_refunded' => $booking->credit_used > 0,
                    'waitlist_position' => null,
                ]);

                // คลาสถูกยกเลิกไม่ใช่ความผิดลูกค้า คืนเครดิตเสมอ
                if ($booking->credit_used > 0) {
                    $this->refundCredit($booking, 'compensate');
                }

                $this->log($booking, $fromStatus, 'cancelled', 'admin', $userId, 'session_cancelled');
                $this->notifySessionCancelled($booking->customer, $session, $reasonTh, $reasonEn);
            }

            $session->update([
                'status' => 'cancelled',
                'cancel_reason_th' => $reasonTh,
                'cancel_reason_en' => $reasonEn,
                'booked_count' => 0,
                'waitlist_count' => 0,
            ]);

            return $bookings->count();
        });
    }

    // ---------- ตรวจสอบเงื่อนไข ----------

    private function assertSessionBookable(ClassSession $session, string $via): void
    {
        if ($session->status !== 'scheduled') {
            throw new BookingException('session_not_available');
        }

        // walk-in คือลูกค้าเดินเข้ามาเรียนสด มักมาถึงตอนคลาสเริ่มไปแล้ว
        // จึงให้บันทึกย้อนได้จนกว่าคลาสจะจบ แต่ไม่ให้ย้อนข้ามคลาสที่จบแล้ว
        if ($via === 'walk_in') {
            if (now()->gte($session->end_at)) {
                throw new BookingException('session_already_ended');
            }
        } elseif (now()->gte($session->start_at)) {
            throw new BookingException('session_already_started');
        }

        // แอดมินจองแทนได้แม้เลยเวลาปิดรับจองแล้ว
        if ($via === 'customer' && $session->booking_closes_at && now()->gt($session->booking_closes_at)) {
            throw new BookingException('booking_closed');
        }

        if ($via === 'customer' && $session->booking_opens_at && now()->lt($session->booking_opens_at)) {
            throw new BookingException('booking_not_open');
        }
    }

    private function assertNotAlreadyBooked(Customer $customer, ClassSession $session): void
    {
        $exists = Booking::where('customer_id', $customer->id)
            ->where('class_session_id', $session->id)
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->exists();

        if ($exists) {
            throw new BookingException('already_booked');
        }
    }

    /** หาแพ็กที่ใช้จองรอบนี้ได้ เรียงจากใกล้หมดอายุก่อน */
    public function resolvePackage(Customer $customer, ClassSession $session): CustomerPackage
    {
        $packages = CustomerPackage::with('package.classTypes')
            ->where('customer_id', $customer->id)
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', now())
            ->whereDate('expires_at', '>=', now())
            ->orderBy('expires_at')
            ->lockForUpdate()
            ->get();

        if ($packages->isEmpty()) {
            throw new BookingException('no_active_package');
        }

        // แพ็กที่ใช้กับคลาสประเภทนี้ไม่ได้ ตัดออกก่อนเลย
        $eligible = $packages->filter(
            fn ($p) => $p->allowsClassType($session->class_type_id)
        );

        if ($eligible->isEmpty()) {
            throw new BookingException('package_not_valid_for_class');
        }

        foreach ($eligible as $package) {
            // ข้ามแพ็กที่กำลังฟรีซอยู่
            if ($package->status === 'frozen') {
                continue;
            }

            if ($package->isUnlimited()) {
                if ($this->passesQuota($customer, $package, $session)) {
                    return $package;
                }

                continue;
            }

            if ($package->credit_remaining >= $session->credit_cost) {
                if ($this->passesQuota($customer, $package, $session)) {
                    return $package;
                }
            }
        }

        // แยกสาเหตุให้ชัด จะได้แจ้งลูกค้าถูก
        $hasUnlimited = $eligible->contains(fn ($p) => $p->isUnlimited());

        throw new BookingException($hasUnlimited ? 'quota_exceeded' : 'insufficient_credit');
    }

    /** เช็คโควตาต่อวัน/สัปดาห์/จองล่วงหน้า สำหรับแพ็ก unlimited */
    private function passesQuota(Customer $customer, CustomerPackage $package, ClassSession $session): bool
    {
        if ($package->max_per_day) {
            $sameDay = Booking::where('customer_id', $customer->id)
                ->where('customer_package_id', $package->id)
                ->whereIn('status', ['confirmed', 'attended'])
                ->whereHas('classSession', fn ($q) => $q->whereDate('start_at', $session->start_at->toDateString()))
                ->count();

            if ($sameDay >= $package->max_per_day) {
                return false;
            }
        }

        if ($package->max_per_week) {
            $weekStart = $session->start_at->copy()->startOfWeek();
            $weekEnd = $session->start_at->copy()->endOfWeek();

            $sameWeek = Booking::where('customer_id', $customer->id)
                ->where('customer_package_id', $package->id)
                ->whereIn('status', ['confirmed', 'attended'])
                ->whereHas('classSession', fn ($q) => $q->whereBetween('start_at', [$weekStart, $weekEnd]))
                ->count();

            if ($sameWeek >= $package->max_per_week) {
                return false;
            }
        }

        if ($package->max_future_bookings) {
            $future = Booking::where('customer_id', $customer->id)
                ->where('customer_package_id', $package->id)
                ->where('status', 'confirmed')
                ->whereHas('classSession', fn ($q) => $q->where('start_at', '>', now()))
                ->count();

            if ($future >= $package->max_future_bookings) {
                return false;
            }
        }

        return true;
    }

    private function isLateCancel(ClassSession $session, string $by): bool
    {
        // แอดมินยกเลิกให้ ไม่คิดว่าช้า
        if ($by === 'admin' || $by === 'system') {
            return false;
        }

        $deadlineHours = (int) Setting::get('cancel_deadline_hours', 12);
        $deadline = $session->start_at->copy()->subHours($deadlineHours);

        return now()->gt($deadline) && Setting::get('late_cancel_charge_credit', true);
    }

    /** เช็คว่ายกเลิกตอนนี้จะเสียเครดิตไหม ใช้โชว์คำเตือนใน UI ก่อนกดยืนยัน */
    public function cancellationPreview(Booking $booking): array
    {
        $session = $booking->classSession;
        $deadlineHours = (int) Setting::get('cancel_deadline_hours', 12);
        $deadline = $session->start_at->copy()->subHours($deadlineHours);
        $isLate = $booking->status !== 'waitlisted' && now()->gt($deadline);

        return [
            'is_late' => $isLate,
            'will_lose_credit' => $isLate && $booking->credit_used > 0,
            'credit_at_stake' => (float) $booking->credit_used,
            'deadline' => $deadline,
            'deadline_hours' => $deadlineHours,
        ];
    }

    // ---------- เครดิต ----------

    private function deductCredit(
        Customer $customer,
        CustomerPackage $package,
        Booking $booking,
        float $amount,
        ClassSession $session,
    ): void {
        $package->decrement('credit_remaining', $amount);
        $package->increment('credit_used', $amount);
        $package->refresh();

        if ($package->credit_remaining <= 0) {
            $package->update(['status' => 'used_up']);
        }

        CreditTransaction::create([
            'customer_id' => $customer->id,
            'customer_package_id' => $package->id,
            'booking_id' => $booking->id,
            'amount' => -$amount,
            'balance_after' => $customer->totalCredits(),
            'type' => 'booking',
            'reason_th' => 'จองคลาส ' . $session->classType->name_th,
            'reason_en' => 'Booked ' . $session->classType->name_en,
        ]);
    }

    private function refundCredit(Booking $booking, string $type): void
    {
        $package = $booking->customerPackage;

        if (! $package || $booking->credit_used <= 0) {
            return;
        }

        $amount = (float) $booking->credit_used;

        $package->increment('credit_remaining', $amount);
        $package->decrement('credit_used', $amount);

        // แพ็กที่เคยใช้หมดแล้ว กลับมาใช้ได้อีกถ้ายังไม่หมดอายุ
        if ($package->status === 'used_up' && $package->fresh()->credit_remaining > 0) {
            $package->update(['status' => 'active']);
        }

        CreditTransaction::create([
            'customer_id' => $booking->customer_id,
            'customer_package_id' => $package->id,
            'booking_id' => $booking->id,
            'amount' => $amount,
            'balance_after' => $booking->customer->totalCredits(),
            'type' => $type,
            'reason_th' => $type === 'compensate' ? 'คลาสถูกยกเลิก คืนเครดิต' : 'ยกเลิกการจองทันเวลา คืนเครดิต',
            'reason_en' => $type === 'compensate' ? 'Class cancelled — credit refunded' : 'Cancelled in time — credit refunded',
        ]);
    }

    private function recordCreditNote(Booking $booking, string $type, string $reasonTh, string $reasonEn): void
    {
        CreditTransaction::create([
            'customer_id' => $booking->customer_id,
            'customer_package_id' => $booking->customer_package_id,
            'booking_id' => $booking->id,
            'amount' => 0,
            'balance_after' => $booking->customer->totalCredits(),
            'type' => $type,
            'reason_th' => $reasonTh,
            'reason_en' => $reasonEn,
        ]);
    }

    // ---------- ประกอบ ----------

    private function log(
        Booking $booking,
        ?string $from,
        string $to,
        string $actorType,
        ?int $actorId = null,
        ?string $reason = null,
    ): void {
        BookingLog::create([
            'booking_id' => $booking->id,
            'from_status' => $from,
            'to_status' => $to,
            'actor_type' => $actorType === 'walk_in' ? 'admin' : $actorType,
            'actor_id' => $actorId,
            'reason' => $reason,
        ]);
    }

    private function notifyBookingConfirmed(Customer $customer, ClassSession $session, Booking $booking): void
    {
        Notification::create([
            'customer_id' => $customer->id,
            'type' => 'booking_confirmed',
            'title_th' => 'ยืนยันการจองแล้ว',
            'title_en' => 'Booking confirmed',
            'body_th' => $session->classType->name_th . ' ' . $session->start_at->format('d/m/Y H:i') . ' ที่' . $session->branch->name_th,
            'body_en' => $session->classType->name_en . ' on ' . $session->start_at->format('d M Y, H:i') . ' at ' . $session->branch->name_en,
            'data' => ['booking_id' => $booking->id, 'session_id' => $session->id],
            'sent_at' => now(),
        ]);
    }

    private function notifyWaitlistPromoted(Customer $customer, ClassSession $session, Booking $booking): void
    {
        Notification::create([
            'customer_id' => $customer->id,
            'type' => 'waitlist_promoted',
            'title_th' => 'คุณได้ที่นั่งแล้ว',
            'title_en' => 'You got a spot',
            'body_th' => 'มีที่ว่างสำหรับ ' . $session->classType->name_th . ' ' . $session->start_at->format('d/m/Y H:i') . ' ระบบยืนยันการจองให้แล้ว',
            'body_en' => 'A spot opened for ' . $session->classType->name_en . ' on ' . $session->start_at->format('d M Y, H:i') . ' — your booking is confirmed.',
            'data' => ['booking_id' => $booking->id, 'session_id' => $session->id],
            'sent_at' => now(),
        ]);
    }

    private function notifySessionCancelled(Customer $customer, ClassSession $session, string $reasonTh, string $reasonEn): void
    {
        Notification::create([
            'customer_id' => $customer->id,
            'type' => 'class_cancelled',
            'title_th' => 'คลาสถูกยกเลิก',
            'title_en' => 'Class cancelled',
            'body_th' => $session->classType->name_th . ' ' . $session->start_at->format('d/m/Y H:i') . ' ถูกยกเลิก (' . $reasonTh . ') เครดิตคืนเข้าบัญชีแล้ว',
            'body_en' => $session->classType->name_en . ' on ' . $session->start_at->format('d M Y, H:i') . ' was cancelled (' . $reasonEn . '). Your credit has been refunded.',
            'data' => ['session_id' => $session->id],
            'sent_at' => now(),
        ]);
    }

    private function notifyClassReminder(Customer $customer, ClassSession $session, Booking $booking): void
    {
        Notification::create([
            'customer_id' => $customer->id,
            'type' => 'class_reminder',
            'title_th' => 'เตือนคลาสที่จองไว้',
            'title_en' => 'Class reminder',
            'body_th' => $session->classType->name_th . ' ' . $session->start_at->format('d/m/Y H:i') . ' ที่' . $session->branch->name_th . ' อย่าลืมมาเรียนนะคะ',
            'body_en' => $session->classType->name_en . ' on ' . $session->start_at->format('d M Y, H:i') . ' at ' . $session->branch->name_en . '. See you there!',
            'data' => ['booking_id' => $booking->id, 'session_id' => $session->id],
            'sent_at' => now(),
        ]);
    }

    public function generateCode(string $prefix): string
    {
        $date = now()->format('ymd');

        return $prefix . '-' . $date . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
