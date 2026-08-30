<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Customer;
use App\Http\Controllers\Trainer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| หน้าแรกสาธารณะ (แนะนำตัว/การตลาด) — ใครก็เข้าดูได้ ไม่ต้องล็อกอิน
|--------------------------------------------------------------------------
*/
Route::get('/', [Customer\LandingController::class, 'index'])->name('landing');
Route::get('/articles/{announcement}', [Customer\LandingController::class, 'article'])->name('articles.show');

/*
|--------------------------------------------------------------------------
| ฝั่งลูกค้า (หน้าแอปหลังบ้าน อยู่ใต้ /customer)
|--------------------------------------------------------------------------
*/
Route::get('/customer', [Customer\HomeController::class, 'index'])->name('home');
Route::get('/schedule', [Customer\ScheduleController::class, 'index'])->name('customer.schedule');
Route::get('/bookings', [Customer\BookingListController::class, 'index'])->name('customer.bookings');
Route::get('/locale/{locale}', [Customer\HomeController::class, 'setLocale'])->name('locale.set');

Route::get('/api/sessions', [Customer\HomeController::class, 'sessions'])->name('api.sessions');

Route::controller(Customer\AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('customer.login');
    Route::post('/login', 'login')->middleware('throttle:10,1');
    Route::get('/register', 'showRegister')->name('customer.register');
    Route::post('/register', 'register')->middleware('throttle:10,1');
    Route::post('/logout', 'logout')->name('customer.logout');
});

// เข้าสู่ระบบ/สมัคร/ผูกบัญชีด้วย LINE
Route::controller(Customer\LineAuthController::class)->group(function () {
    Route::get('/auth/line', 'redirect')->name('customer.line.redirect');
    Route::get('/auth/line/callback', 'callback')->name('customer.line.callback');
});

// หน้ากรอกข้อมูลหลังสมัครด้วย LINE — ห้ามใส่ profile.complete ไม่งั้นวนลูป
Route::middleware('auth:customer')->group(function () {
    Route::controller(Customer\LineAuthController::class)->group(function () {
        Route::get('/profile/complete', 'showComplete')->name('customer.profile.complete');
        Route::post('/profile/complete', 'submitPhone')
            ->middleware('throttle:10,1')->name('customer.profile.complete.submit');
        Route::post('/profile/complete/verify', 'verifyOtp')
            ->middleware('throttle:10,1')->name('customer.line.otp.verify');
        Route::post('/profile/complete/resend', 'resendOtp')
            ->middleware('throttle:5,1')->name('customer.line.otp.resend');
        Route::post('/profile/complete/cancel', 'cancelMerge')->name('customer.line.merge.cancel');
        Route::post('/profile/line/unlink', 'unlink')->name('customer.line.unlink');
    });
});

// ต้องกรอกข้อมูลให้ครบก่อนถึงจะจองได้
Route::middleware(['auth:customer', 'profile.complete'])->group(function () {
    Route::post('/sessions/{session}/book', [Customer\HomeController::class, 'book'])->name('customer.book');
    Route::get('/bookings/{booking}/cancel-preview', [Customer\HomeController::class, 'cancelPreview'])->name('customer.cancel.preview');
    Route::post('/bookings/{booking}/cancel', [Customer\HomeController::class, 'cancel'])->name('customer.cancel');

    Route::controller(Customer\ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('customer.profile.index');
        Route::get('/profile/edit', 'edit')->name('customer.profile.edit');
        Route::put('/profile', 'update')->name('customer.profile.update');
        Route::put('/profile/password', 'updatePassword')->name('customer.profile.password');
    });
});

/*
|--------------------------------------------------------------------------
| ตารางสอนของครู เปิดดูด้วย token ไม่ต้องล็อกอิน
|--------------------------------------------------------------------------
*/
Route::prefix('trainer/{token}')->name('trainer.')->group(function () {
    Route::get('/', [Trainer\ScheduleController::class, 'show'])->name('schedule');
    Route::get('/session/{session}', [Trainer\ScheduleController::class, 'session'])->name('session');
});

/*
|--------------------------------------------------------------------------
| ฝั่งแอดมิน
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::controller(Admin\AuthController::class)->group(function () {
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login')->middleware('throttle:10,1');
        Route::post('/logout', 'logout')->name('logout')->middleware('auth');
    });

    Route::middleware(['auth', 'admin.active'])->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // รอบเรียน
        Route::controller(Admin\ClassSessionController::class)->prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{session}', 'show')->name('show');
            Route::get('/{session}/edit', 'edit')->name('edit');
            Route::put('/{session}', 'update')->name('update');
            Route::post('/{session}/substitute', 'setSubstitute')->name('substitute');
            Route::post('/{session}/cancel', 'cancel')->name('cancel');
            Route::post('/{session}/book', 'bookForCustomer')->name('book');
        });

        // ตารางประจำสัปดาห์
        Route::post('/schedules/generate', [Admin\ClassScheduleController::class, 'generate'])->name('schedules.generate');
        Route::resource('schedules', Admin\ClassScheduleController::class)->except(['show']);

        // การจอง
        Route::controller(Admin\BookingController::class)->prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{booking}/check-in', 'checkIn')->name('checkin');
            Route::post('/{booking}/no-show', 'noShow')->name('noshow');
            Route::post('/{booking}/cancel', 'cancel')->name('cancel');
        });

        // ลูกค้า
        Route::post('/customers/{customer}/credit', [Admin\CustomerController::class, 'adjustCredit'])->name('customers.credit');
        Route::post('/customer-packages/{customerPackage}/freeze', [Admin\CustomerController::class, 'toggleFreeze'])->name('customers.freeze');
        Route::resource('customers', Admin\CustomerController::class)->except(['destroy']);

        // คำสั่งซื้อและการชำระเงิน
        Route::controller(Admin\OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{order}', 'show')->name('show');
            Route::post('/{order}/payments', 'addPayment')->name('payments.add');
            Route::post('/{order}/cancel', 'cancel')->name('cancel');
        });
        Route::post('/payments/{payment}/verify', [Admin\OrderController::class, 'verifyPayment'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [Admin\OrderController::class, 'rejectPayment'])->name('payments.reject');

        // ข้อมูลหลัก
        Route::post('/branches/{branch}/rooms', [Admin\BranchController::class, 'storeRoom'])->name('branches.rooms.store');
        Route::put('/rooms/{room}', [Admin\BranchController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{room}', [Admin\BranchController::class, 'destroyRoom'])->name('rooms.destroy');
        Route::resource('branches', Admin\BranchController::class)->except(['show']);

        Route::post('/trainers/{trainer}/regenerate-token', [Admin\TrainerController::class, 'regenerateToken'])->name('trainers.regenerate');
        Route::resource('trainers', Admin\TrainerController::class)->except(['show']);

        Route::resource('class-types', Admin\ClassTypeController::class)->except(['show']);
        Route::resource('packages', Admin\PackageController::class)->except(['show']);
        Route::resource('announcements', Admin\AnnouncementController::class)->except(['show']);

        Route::get('/holidays', [Admin\HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [Admin\HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [Admin\HolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');

        // เฉพาะเจ้าของระบบ
        Route::middleware('owner')->group(function () {
            Route::resource('users', Admin\UserController::class)->except(['show']);
            Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        });
    });
});
