<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simpanans', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        Schema::table('angsurans', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('simpanans', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('angsurans', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};