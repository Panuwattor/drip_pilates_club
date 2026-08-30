<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ClassType;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::withCount(['classTypes', 'branches'])
            ->with('branches:id,short_name_th,name_th')
            ->orderBy('sort_order')
            ->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.form', [
            'package' => new Package(['type' => 'credit_pack', 'valid_days' => 90]),
            'classTypes' => ClassType::active()->orderBy('sort_order')->get(),
            'branches' => Branch::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        [$classTypeIds, $branchIds] = $this->pullRelations($data);

        $package = Package::create($data);
        $package->classTypes()->sync($data['all_class_types'] ? [] : $classTypeIds);
        $package->branches()->sync($data['all_branches'] ? [] : $branchIds);

        return redirect()->route('admin.packages.index')
            ->with('status', 'เพิ่มแพ็กเกจเรียบร้อยแล้ว');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', [
            'package' => $package,
            'classTypes' => ClassType::active()->orderBy('sort_order')->get(),
            'branches' => Branch::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $data = $this->validated($request, $package);
        [$classTypeIds, $branchIds] = $this->pullRelations($data);

        $package->update($data);
        $package->classTypes()->sync($data['all_class_types'] ? [] : $classTypeIds);
        $package->branches()->sync($data['all_branches'] ? [] : $branchIds);

        return back()->with('status', 'บันทึกแพ็กเกจแล้ว (แพ็กที่ลูกค้าซื้อไปแล้วไม่เปลี่ยนตาม)');
    }

    /** ดึง id ความสัมพันธ์ออกจาก $data เพราะไม่ใช่คอลัมน์ในตาราง */
    private function pullRelations(array &$data): array
    {
        $classTypeIds = $data['class_type_ids'] ?? [];
        $branchIds = $data['branch_ids'] ?? [];
        unset($data['class_type_ids'], $data['branch_ids']);

        return [$classTypeIds, $branchIds];
    }

    public function destroy(Package $package)
    {
        if ($package->id && \App\Models\CustomerPackage::where('package_id', $package->id)->exists()) {
            return back()->with('error', 'ลบไม่ได้ เพราะมีลูกค้าซื้อแพ็กนี้ไปแล้ว ให้ปิดใช้งานแทน');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')->with('status', 'ลบแพ็กเกจแล้ว');
    }

    private function validated(Request $request, ?Package $package = null): array
    {
        $unique = 'unique:packages,code' . ($package ? ',' . $package->id : '');

        $rules = [
            'code' => ['required', 'string', 'max:40', 'alpha_dash', $unique],
            'name_th' => ['required', 'string', 'max:150'],
            'name_en' => ['required', 'string', 'max:150'],
            'description_th' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'type' => ['required', 'in:credit_pack,unlimited,trial'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'price_per_class' => ['nullable', 'numeric', 'min:0'],
            'valid_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'valid_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'max_per_day' => ['nullable', 'integer', 'min:1', 'max:20'],
            'max_per_week' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_future_bookings' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'class_type_ids' => ['nullable', 'array'],
            'class_type_ids.*' => ['exists:class_types,id'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
        ];

        // แพ็กเหมาจ่ายไม่ต้องระบุจำนวนเครดิต
        $rules['credit_amount'] = $request->input('type') === 'unlimited'
            ? ['nullable']
            : ['required', 'integer', 'min:1', 'max:999'];

        $data = $request->validate($rules);

        if ($data['type'] === 'unlimited') {
            $data['credit_amount'] = null;
        }

        return $data + [
            'all_class_types' => $request->boolean('all_class_types', true),
            'all_branches' => $request->boolean('all_branches', true),
            'once_per_customer' => $request->boolean('once_per_customer'),
            'is_public' => $request->boolean('is_public'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
