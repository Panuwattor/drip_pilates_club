<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.edit', [
            'settings' => $settings,
            'groupLabels' => [
                'booking' => __t('การจอง', 'Booking'),
                'cancellation' => __t('การยกเลิก', 'Cancellation'),
                'waitlist' => __t('คิวสำรอง', 'Waitlist'),
                'package' => __t('แพ็กเกจ', 'Packages'),
                'payment' => __t('บัญชีรับเงิน/การชำระเงิน', 'Payment details'),
                'general' => __t('ทั่วไป', 'General'),
                'contact' => __t('ช่องทางติดต่อ', 'Contact channels'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $values = $request->input('settings', []);

        foreach ($values as $key => $value) {
            $setting = Setting::find($key);

            if (! $setting) {
                continue;
            }

            // checkbox ที่ไม่ติ๊กจะไม่ถูกส่งมา จัดการแยก
            if ($setting->type === 'bool') {
                continue;
            }

            if ($setting->type === 'int' && ! is_numeric($value)) {
                continue;
            }

            Setting::put($key, $value);
        }

        // จัดการ bool ทั้งหมดจากรายชื่อที่ส่งมา
        foreach (Setting::where('type', 'bool')->pluck('key') as $key) {
            Setting::put($key, $request->boolean("settings.{$key}") ? 'true' : 'false');
        }

        return back()->with('status', __t('บันทึกการตั้งค่าแล้ว', 'Settings saved'));
    }
}
