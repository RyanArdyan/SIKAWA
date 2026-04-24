<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tim_kerja', function (Blueprint $table) {
            // Menambahkan kolom ketua_id yang terhubung ke tabel users
            $table->foreignId('ketua_id')->nullable()->after('nama')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tim_kerja', function (Blueprint $table) {
            $table->dropForeign(['ketua_id']);
            $table->dropColumn('ketua_id');
        });
    }
};
