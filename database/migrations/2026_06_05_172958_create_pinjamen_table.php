<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjamans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anggota_id')
                ->constrained('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('tanggal_pinjaman');

            $table->string('jenis_pinjaman', 20);
            
            $table->decimal('nominal_pinjaman', 15, 2);

            $table->decimal('persentase_jasa', 5, 2);

            $table->decimal('sisa_pinjaman', 15, 2);

            $table->string('status', 20)
                ->default('aktif');

            $table->text('keterangan')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'anggota_id',
                'jenis_pinjaman',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};