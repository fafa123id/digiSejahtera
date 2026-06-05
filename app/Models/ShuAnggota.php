<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuAnggota extends Model
{
    use HasFactory;
    protected $table = 'shu_anggotas';
    protected $fillable = [
        'anggota_id',
        'tahun',
        'total_simpanan',
        'total_pinjaman',
        'persentase_simpanan',
        'persentase_pinjaman',
        'shu_simpanan',
        'shu_pinjaman',
        'total_shu',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'total_simpanan' => 'decimal:2',
            'total_pinjaman' => 'decimal:2',
            'persentase_simpanan' => 'decimal:2',
            'persentase_pinjaman' => 'decimal:2',
            'shu_simpanan' => 'decimal:2',
            'shu_pinjaman' => 'decimal:2',
            'total_shu' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
