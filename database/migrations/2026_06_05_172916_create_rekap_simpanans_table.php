<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_simpanans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anggota_id')
                ->unique()
                ->constrained('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('total_simpanan_pokok', 15, 2)
                ->default(0);

            $table->decimal('total_simpanan_wajib', 15, 2)
                ->default(0);

            $table->decimal('total_simpanan_sukarela', 15, 2)
                ->default(0);

            $table->decimal('total_simpanan_hari_raya', 15, 2)
                ->default(0);

            $table->decimal('total_simpanan_rekreasi', 15, 2)
                ->default(0);

            $table->decimal('total_simpanan', 15, 2)
                ->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_simpanans');
    }
};