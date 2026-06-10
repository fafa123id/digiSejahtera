<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggotas', function (Blueprint $table): void {
            $table->string('agama', 10)->default('islam')->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table): void {
            $table->dropColumn('agama');
        });
    }
};