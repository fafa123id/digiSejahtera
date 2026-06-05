<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shu_anggotas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anggota_id')
                ->constrained('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedSmallInteger('tahun');

            $table->decimal('total_simpanan', 15, 2)
                ->default(0);

            $table->decimal('total_pinjaman', 15, 2)
                ->default(0);

            $table->decimal('persentase_simpanan', 5, 2)
                ->default(50);

            $table->decimal('persentase_pinjaman', 5, 2)
                ->default(50);

            $table->decimal('shu_simpanan', 15, 2)
                ->default(0);

            $table->decimal('shu_pinjaman', 15, 2)
                ->default(0);

            $table->decimal('total_shu', 15, 2)
                ->default(0);

            $table->timestamp('calculated_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'anggota_id',
                'tahun',
            ]);

            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shu_anggotas');
    }
};