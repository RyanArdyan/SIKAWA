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
            // Menambahkan location_id setelah kolom tim_kerja_id agar rapi
            $table->unsignedBigInteger('location_id')->nullable()->after('tim_kerja_id');

            // Mendefinisikan foreign key
            $table->foreign('location_id')
                  ->references('id')
                  ->on('locations')
                  ->onDelete('set null'); // Jika lokasi dihapus, id di users jadi null (tidak error)
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
