<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimKerja extends Model
{
    protected $table = "tim_kerja";
    protected $guarded = [];

    // Relasi untuk mengambil data Ketua Tim
    // Karena di migrasi kamu namanya 'ketua_id'
    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    // Relasi untuk mengambil semua anggota di tim tersebut
    // Karena di migrasi users kamu namanya 'tim_kerja_id'
    public function anggota()
    {
        return $this->hasMany(User::class, 'tim_kerja_id');
    }
}
