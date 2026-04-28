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
        Schema::table('attendances', function (Blueprint $table) {
            // 1. Tambah kolom baru
            if (! Schema::hasColumn('attendances', 'tipe_absen')) {
                $table->enum('tipe_absen', ['WFO', 'WFA'])->default('WFA')->after('status');
            }

            if (! Schema::hasColumn('attendances', 'ip_address_log')) {
                $table->string('ip_address_log')->nullable()->after('tipe_absen');
            }

            // 2. Mengubah kolom lama menjadi nullable (Penting untuk mode WFO)
            // Gunakan sintaks ini agar lebih kompatibel dengan Laravel versi terbaru
            $table->string('photo_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
        });
    }
};
