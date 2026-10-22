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
        Schema::create('lembur', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users berdasarkan kolom id[cite: 6]
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamp('check_in_lembur')->nullable();
            $table->timestamp('check_out_lembur')->nullable();
            $table->string('photo_path_in')->nullable();
            $table->string('photo_path_out')->nullable();
            $table->text('keterangan_lembur')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembur');
    }
};
