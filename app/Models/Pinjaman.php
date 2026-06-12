<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    use HasFactory;

    public const JENIS_REGULER = 'reguler';
    public const JENIS_SEBRAK = 'sebrak';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_LUNAS = 'lunas';

    protected $table = 'pinjamans';
    protected $fillable = [
        'anggota_id',
        'tanggal_pinjaman',
        'jenis_pinjaman',
        'nominal_pinjaman',
        'persentase_jasa',
        'sisa_pinjaman',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjaman' => 'date',
            'nominal_pinjaman' => 'decimal:2',
            'persentase_jasa' => 'decimal:2',
            'sisa_pinjaman' => 'decimal:2',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function angsurans(): HasMany
    {
        return $this->hasMany(Angsuran::class);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function scopeReguler(Builder $query): Builder
    {
        return $query->where('jenis_pinjaman', self::JENIS_REGULER);
    }

    public function scopeSebrak(Builder $query): Builder
    {
        return $query->where('jenis_pinjaman', self::JENIS_SEBRAK);
    }

    public function sudahLunas(): bool
    {
        return (float) $this->sisa_pinjaman <= 0;
    }
}