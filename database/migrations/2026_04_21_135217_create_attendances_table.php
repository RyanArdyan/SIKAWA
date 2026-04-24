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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Kolom untuk Masuk
            $table->string('photo_path'); // Foto saat masuk
            $table->timestamp('check_in_time');

            // Kolom untuk Pulang (Kita buat nullable karena saat masuk, data ini masih kosong)
            $table->timestamp('check_out_time')->nullable();
            $table->string('photo_path_out')->nullable(); // Foto saat pulang
            $table->string('laporan_pdf')->nullable();

            // Status & Keterangan
            $table->enum('status', ['hadir', 'terlambat'])->default('hadir');

            // Kita gunakan string agar aman, atau double untuk presisi tinggi
            $table->string('latitude_in')->nullable();
            $table->string('longitude_in')->nullable();
            $table->string('latitude_out')->nullable();
            $table->string('longitude_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
