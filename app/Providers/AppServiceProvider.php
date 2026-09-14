<?php

namespace App\Providers;

use App\Models\Payment;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ตัวเลขสลิปรออนุมัติบนเมนูแอดมิน ต้องเห็นทุกหน้า ไม่ใช่แค่หน้าแดชบอร์ด
        // เพราะลูกค้าส่งสลิปเข้ามาเองได้ตลอดเวลา ปล่อยค้างไว้ลูกค้ารอเครดิต
        View::composer('admin.layouts.app', function ($view) {
            if (! array_key_exists('pendingPayments', $view->getData())) {
                $view->with('pendingPayments', Payment::where('status', 'pending')->count());
            }
        });
    }
}
