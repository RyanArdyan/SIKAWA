<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lembur extends Model
{
    use HasFactory;

    // Mendefinisikan nama tabel secara eksplisit karena bentuk tunggal (singular)
    protected $table = 'lembur';
    protected $guarded = [];
    /**
     * Relasi ke model User (Setiap data lembur milik satu pegawai/user)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
