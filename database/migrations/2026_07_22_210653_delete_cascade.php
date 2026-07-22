<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('simpanans', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });


        Schema::table('rekap_simpanans', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });


        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });


        Schema::table('angsurans', function (Blueprint $table): void {
            $table->dropForeign([
                'pinjaman_id',
            ]);

            $table->foreign('pinjaman_id')
                ->references('id')
                ->on('pinjamans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });


        Schema::table('shu_anggotas', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {

        Schema::table('shu_anggotas', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('angsurans', function (Blueprint $table): void {
            $table->dropForeign([
                'pinjaman_id',
            ]);

            $table->foreign('pinjaman_id')
                ->references('id')
                ->on('pinjamans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('rekap_simpanans', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('simpanans', function (Blueprint $table): void {
            $table->dropForeign([
                'anggota_id',
            ]);

            $table->foreign('anggota_id')
                ->references('id')
                ->on('anggotas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
};