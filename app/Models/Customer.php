<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $guard = 'customer';

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token', 'admin_note'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_pregnant' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'line_linked_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function hasLineLinked(): bool
    {
        return filled($this->line_user_id);
    }

    /**
     * ข้อมูลขั้นต่ำที่ต้องมีก่อนใช้งานระบบได้ = ชื่อ + เบอร์โทร
     * คนที่สมัครผ่าน LINE จะยังไม่มีเบอร์ ต้องเด้งไปกรอกก่อน
     */
    public function needsProfileCompletion(): bool
    {
        return blank($this->phone) || blank($this->first_name);
    }

    /** ล็อกอินได้ด้วยรหัสผ่านไหม คนที่สมัครผ่าน LINE ล้วนๆ จะยังไม่มีรหัส */
    public function hasPassword(): bool
    {
        return filled($this->password);
    }

    public function markProfileCompleted(): void
    {
        $this->forceFill(['profile_completed_at' => now()])->save();
    }

    public function homeBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'home_branch_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(CustomerPackage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /** แพ็กที่ใช้จองได้ตอนนี้ เรียงตามใกล้หมดอายุก่อน */
    public function usablePackages()
    {
        return $this->packages()
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', now())
            ->whereDate('expires_at', '>=', now())
            ->where(function ($q) {
                $q->where('type', 'unlimited')
                  ->orWhere('credit_remaining', '>', 0);
            })
            ->orderBy('expires_at');
    }

    /** ยอดเครดิตคงเหลือรวม (unlimited ไม่นับเป็นตัวเลข) */
    public function totalCredits(): int
    {
        return (int) $this->packages()
            ->where('status', 'active')
            ->where('type', '!=', 'unlimited')
            ->whereDate('expires_at', '>=', now())
            ->sum('credit_remaining');
    }

    public function hasUnlimited(): bool
    {
        return $this->packages()
            ->where('status', 'active')
            ->where('type', 'unlimited')
            ->whereDate('starts_at', '<=', now())
            ->whereDate('expires_at', '>=', now())
            ->exists();
    }
}
