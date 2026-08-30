<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerificationCode extends Model
{
    protected $guarded = [];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
