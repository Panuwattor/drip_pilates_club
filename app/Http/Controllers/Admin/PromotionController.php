<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::withCount('packages')
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin.promotions.form', [
            'promotion' => new Promotion([
                'discount_type' => 'percent',
                'applies_to_all' => true,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->endOfMonth(),
            ]),
            'packages' => Package::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $packageIds = $this->pullPackages($data);

        $promotion = Promotion::create($data);
        $promotion->packages()->sync($data['applies_to_all'] ? [] : $packageIds);

        return redirect()->route('admin.promotions.index')
            ->with('status', __t('เพิ่มโปรโมชั่นเรียบร้อยแล้ว', 'Promotion added'));
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.form', [
            'promotion' => $promotion->load('packages:id'),
            'packages' => Package::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validated($request, $promotion);
        $packageIds = $this->pullPackages($data);

        $promotion->update($data);
        $promotion->packages()->sync($data['applies_to_all'] ? [] : $packageIds);

        return back()->with('status', __t('บันทึกโปรโมชั่นแล้ว', 'Promotion saved'));
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('status', __t('ลบโปรโมชั่นแล้ว', 'Promotion deleted'));
    }

    /** ดึง id แพ็กออกจาก $data เพราะไม่ใช่คอลัมน์ในตาราง */
    private function pullPackages(array &$data): array
    {
        $packageIds = $data['package_ids'] ?? [];
        unset($data['package_ids']);

        return $packageIds;
    }

    private function validated(Request $request, ?Promotion $promotion = null): array
    {
        $unique = 'unique:promotions,code' . ($promotion ? ',' . $promotion->id : '');

        $rules = [
            'code' => ['required', 'string', 'max:40', 'alpha_dash', $unique],
            'name_th' => ['required', 'string', 'max:150'],
            'name_en' => ['required', 'string', 'max:150'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['exists:packages,id'],
        ];

        // เปอร์เซ็นต์จำกัดไม่เกิน 100 ส่วนลดแบบบาทไม่จำกัดเพดาน
        $rules['discount_value'] = $request->input('discount_type') === 'percent'
            ? ['required', 'numeric', 'min:0', 'max:100']
            : ['required', 'numeric', 'min:0'];

        $data = $request->validate($rules);

        return $data + [
            'applies_to_all' => $request->boolean('applies_to_all'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }
}
