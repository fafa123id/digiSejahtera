<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Angsuran extends Model
{
    use HasFactory;

    protected $table =
        'angsurans';

    protected $fillable = [
        'pinjaman_id',
        'periode',
        'tanggal_pembayaran',
        'angsuran_ke',
        'saldo_awal',
        'nominal_angsuran',
        'persentase_jasa',
        'jasa_pinjaman',
        'sisa_pinjaman',
        'jumlah_tagihan',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'periode' =>
                'date',

            'tanggal_pembayaran' =>
                'date',

            'saldo_awal' =>
                'decimal:2',

            'nominal_angsuran' =>
                'decimal:2',

            'persentase_jasa' =>
                'decimal:2',

            'jasa_pinjaman' =>
                'decimal:2',

            'sisa_pinjaman' =>
                'decimal:2',

            'jumlah_tagihan' =>
                'decimal:2',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(
            Pinjaman::class
        );
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}