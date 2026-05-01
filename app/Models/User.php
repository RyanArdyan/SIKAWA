<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; // Tambahan untuk helper foto

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom yang tidak boleh diisi secara mass-assignment.
     * Karena menggunakan $guarded = [], berarti semua kolom boleh diisi.
     */
    protected $guarded = [];

    /**
     * Kolom yang harus disembunyikan saat serialisasi (JSON/Array).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relasi ke tabel Tim Kerja.
     */
    public function tim_kerja(): BelongsTo
    {
        return $this->belongsTo(TimKerja::class, 'tim_kerja_id');
    }

    /**
     * Relasi ke tabel Absensi (Attendance).
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Casting atribut (otomatis mengubah tipe data saat diakses).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * HELPER BARU: Mendapatkan URL foto profil.
     * Memudahkan Anda memanggil $user->avatar_url di file Blade.
     */
    public function getAvatarUrlAttribute()
    {
        return $this->foto_path
            ? Storage::url($this->foto_path)
            : asset('images/default-avatar.png');
    }

    /**
     * Fungsi Otorisasi: Mengecek apakah user adalah Admin atau Super Admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Fungsi Otorisasi: Mengecek apakah user adalah Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Fungsi Otorisasi: Mengecek apakah user adalah Pegawai biasa.
     */
    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }
}
