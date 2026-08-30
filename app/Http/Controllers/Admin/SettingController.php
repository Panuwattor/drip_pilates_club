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
                'booking' => 'การจอง',
                'cancellation' => 'การยกเลิก',
                'waitlist' => 'คิวสำรอง',
                'package' => 'แพ็กเกจ',
                'general' => 'ทั่วไป',
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

        return back()->with('status', 'บันทึกการตั้งค่าแล้ว');
    }
}
