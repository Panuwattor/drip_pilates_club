<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * ทีมงานหลังบ้าน (แอดมิน) ใช้ guard 'web' ที่ Laravel ให้มา
 * ลูกค้าอยู่ตาราง customers แยกต่างหาก ใช้ guard 'customer'
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /** owner กับ manager เห็นทุกสาขา ส่วน staff เห็นเฉพาะสาขาตัวเอง */
    public function canAccessAllBranches(): bool
    {
        return in_array($this->role, ['owner', 'manager'], true);
    }

    /** สาขาที่ผู้ใช้คนนี้มีสิทธิ์เข้าถึง */
    public function accessibleBranchIds(): array
    {
        if ($this->canAccessAllBranches()) {
            return Branch::pluck('id')->all();
        }

        return $this->branch_id ? [$this->branch_id] : [];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
