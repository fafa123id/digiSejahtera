<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapSimpanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'anggota_id',
        'total_simpanan_pokok',
        'total_simpanan_wajib',
        'total_simpanan_sukarela',
        'total_simpanan_hari_raya',
        'total_simpanan_rekreasi',
        'total_simpanan',
    ];

    protected function casts(): array
    {
        return [
            'total_simpanan_pokok' => 'decimal:2',
            'total_simpanan_wajib' => 'decimal:2',
            'total_simpanan_sukarela' => 'decimal:2',
            'total_simpanan_hari_raya' => 'decimal:2',
            'total_simpanan_rekreasi' => 'decimal:2',
            'total_simpanan' => 'decimal:2',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}