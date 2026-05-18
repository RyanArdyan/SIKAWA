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
        Schema::table('users', function (Blueprint $table) {
            // Kolom baru diletakkan setelah kolom 'tim_kerja_id' (opsional agar rapi di DB)
            $table->string('pangkat_golongan')->nullable()->after('name');
            $table->string('jabatan')->nullable()->after('pangkat_golongan');
            $table->string('kelas_jabatan')->nullable()->after('jabatan');
            $table->string('pendidikan')->nullable()->after('kelas_jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
