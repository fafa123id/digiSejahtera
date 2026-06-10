<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shu_anggotas', function (Blueprint $table): void {
            $table->renameColumn('total_pinjaman', 'total_jasa_pinjaman');
            $table->renameColumn('persentase_pinjaman', 'persentase_jasa_pinjaman');
        });
    }

    public function down(): void
    {
        Schema::table('shu_anggotas', function (Blueprint $table): void {
            $table->renameColumn('total_jasa_pinjaman', 'total_pinjaman');
            $table->renameColumn('persentase_jasa_pinjaman', 'persentase_pinjaman');
        });
    }
};