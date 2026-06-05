<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_anggota', 20)
                ->unique();

            $table->string('nama', 150);


            $table->date('tanggal_masuk')
                ->nullable();

            $table->date('tanggal_keluar')
                ->nullable();

            $table->string('status', 20)
                ->default('aktif');

            $table->timestamps();
            $table->softDeletes();

            $table->index('nama');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};