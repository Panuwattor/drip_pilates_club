<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Trainer;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = Trainer::with('branches')
            ->withCount(['classSessions' => fn ($q) => $q->where('start_at', '>=', now())])
            ->orderBy('sort_order')
            ->get();

        return view('admin.trainers.index', compact('trainers'));
    }

    public function create()
    {
        return view('admin.trainers.form', [
            'trainer' => new Trainer,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $branchIds = $data['branch_ids'] ?? [];
        unset($data['branch_ids']);

        $data['avatar'] = $this->handleAvatar($request);

        $trainer = Trainer::create($data);
        $trainer->branches()->sync($branchIds);

        return redirect()->route('admin.trainers.edit', $trainer)
            ->with('status', 'เพิ่มครูผู้สอนเรียบร้อยแล้ว');
    }

    public function edit(Trainer $trainer)
    {
        return view('admin.trainers.form', [
            'trainer' => $trainer,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Trainer $trainer)
    {
        $data = $this->validated($request, $trainer);
        $branchIds = $data['branch_ids'] ?? [];
        unset($data['branch_ids']);

        if ($avatar = $this->handleAvatar($request)) {
            $data['avatar'] = $avatar;
        }

        $trainer->update($data);
        $trainer->branches()->sync($branchIds);

        return back()->with('status', 'บันทึกข้อมูลครูแล้ว');
    }

    public function destroy(Trainer $trainer)
    {
        if ($trainer->classSessions()->where('start_at', '>=', now())->exists()) {
            return back()->with('error', 'ลบไม่ได้ เพราะครูคนนี้มีตารางสอนอยู่ ให้ปิดใช้งานแทน');
        }

        $trainer->delete();

        return redirect()->route('admin.trainers.index')->with('status', 'ลบครูผู้สอนแล้ว');
    }

    /** สร้างลิงก์ตารางส่วนตัวใหม่ ใช้เวลาลิงก์เดิมหลุด */
    public function regenerateToken(Trainer $trainer)
    {
        $trainer->regeneratePublicToken();

        return back()->with('status', 'สร้างลิงก์ใหม่แล้ว ลิงก์เดิมใช้ไม่ได้อีกต่อไป');
    }

    private function handleAvatar(Request $request): ?string
    {
        if (! $request->hasFile('avatar_file')) {
            return null;
        }

        return MediaStorage::store($request->file('avatar_file'), 'trainers');
    }

    private function validated(Request $request, ?Trainer $trainer = null): array
    {
        $unique = 'unique:trainers,code' . ($trainer ? ',' . $trainer->id : '');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', $unique],
            'name_th' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'nickname_th' => ['nullable', 'string', 'max:60'],
            'nickname_en' => ['nullable', 'string', 'max:60'],
            'bio_th' => ['nullable', 'string'],
            'bio_en' => ['nullable', 'string'],
            'specialties_th' => ['nullable', 'string', 'max:255'],
            'specialties_en' => ['nullable', 'string', 'max:255'],
            'certifications_th' => ['nullable', 'string', 'max:255'],
            'certifications_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
            'avatar_file' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($data['avatar_file']);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
