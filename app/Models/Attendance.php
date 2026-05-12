<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Casting atribut untuk memastikan check_in_time dan check_out_time
     * otomatis menjadi objek Carbon saat diakses.
     */
    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    protected $guarded = [];

    /**
     * Relasi ke model User (Pegawai).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Fungsi pembantu untuk mengecek apakah durasi kerja sudah 8 jam.
     * Digunakan untuk validasi sebelum absen pulang.
     */
    public function isWorkDurationMet()
    {
        if (! $this->check_in_time) {
            return false;
        }

        // Karena sudah di-cast ke 'datetime', kita tidak perlu Carbon::parse lagi
        return now()->diffInHours($this->check_in_time) >= 8;
    }

    /**
     * Mendapatkan waktu minimal untuk diperbolehkan pulang (8 jam kerja).
     */
    public function getMinCheckOutTime()
    {
        if (! $this->check_in_time) {
            return null;
        }

        return $this->check_in_time->addHours(8)->format('H:i');
    }

    /**
     * HELPER BARU: Mendapatkan waktu minimal untuk diperbolehkan upload laporan.
     * Sesuai kebijakan: 1 jam setelah absen masuk.
     */
    public function getMinUploadTime()
    {
        if (! $this->check_in_time) {
            return null;
        }

        return $this->check_in_time->addHour()->format('H:i');
    }

    // Relasi ke Location (Baru)
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
