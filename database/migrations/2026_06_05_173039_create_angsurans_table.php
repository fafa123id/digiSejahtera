<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angsurans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pinjaman_id')
                ->constrained('pinjamans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('periode');

            $table->date('tanggal_pembayaran');

            $table->unsignedInteger('angsuran_ke')
                ->nullable();

            $table->decimal('saldo_awal', 15, 2);

            $table->decimal('nominal_angsuran', 15, 2)
                ->default(0);

            $table->decimal('persentase_jasa', 5, 2);

            $table->decimal('jasa_pinjaman', 15, 2)
                ->default(0);

            $table->decimal('sisa_pinjaman', 15, 2);

            $table->decimal('jumlah_tagihan', 15, 2)
                ->default(0);

            $table->text('keterangan')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'pinjaman_id',
                'periode',
            ]);

            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsurans');
    }
};