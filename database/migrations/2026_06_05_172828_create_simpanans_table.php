<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simpanans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anggota_id')
                ->constrained('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('periode');

            $table->decimal('simpanan_pokok', 15, 2)
                ->default(0);

            $table->decimal('simpanan_wajib', 15, 2)
                ->default(0);

            $table->decimal('simpanan_sukarela', 15, 2)
                ->default(0);

            $table->decimal('simpanan_hari_raya', 15, 2)
                ->default(0);

            $table->decimal('simpanan_rekreasi', 15, 2)
                ->default(0);

            $table->decimal('jumlah_simpanan', 15, 2)
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
                'anggota_id',
                'periode',
            ]);

            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpanans');
    }
};