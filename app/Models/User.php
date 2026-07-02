<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PENGURUS = 'pengurus';

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPengurus(): bool
    {
        return $this->role === self::ROLE_PENGURUS;
    }
        public function simpanansDibuat(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'created_by');
    }

    public function pinjamansDibuat(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'created_by');
    }

    public function angsuransDibuat(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'created_by');
    }

}