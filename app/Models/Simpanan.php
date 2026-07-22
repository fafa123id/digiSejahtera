<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Simpanan extends Model
{
    use HasFactory;
    protected $table = 'simpanans';

    protected $fillable = [
        'anggota_id',
        'periode',
        'simpanan_pokok',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_hari_raya',
        'simpanan_rekreasi',
        'jumlah_simpanan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'periode' => 'date',
            'simpanan_pokok' => 'decimal:2',
            'simpanan_wajib' => 'decimal:2',
            'simpanan_sukarela' => 'decimal:2',
            'simpanan_hari_raya' => 'decimal:2',
            'simpanan_rekreasi' => 'decimal:2',
            'jumlah_simpanan' => 'decimal:2',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

        public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}