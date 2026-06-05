<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anggota extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $fillable = [
        'nomor_anggota',
        'nama',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

    public function simpanans(): HasMany
    {
        return $this->hasMany(Simpanan::class);
    }

    public function rekapSimpanan(): HasOne
    {
        return $this->hasOne(RekapSimpanan::class);
    }

    public function pinjamans(): HasMany
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function shuAnggotas(): HasMany
    {
        return $this->hasMany(ShuAnggota::class);
    }

    public function pinjamanReguler(): HasMany
    {
        return $this->hasMany(Pinjaman::class)
            ->where('jenis_pinjaman', Pinjaman::JENIS_REGULER);
    }

    public function pinjamanSebrak(): HasMany
    {
        return $this->hasMany(Pinjaman::class)
            ->where('jenis_pinjaman', Pinjaman::JENIS_SEBRAK);
    }
}