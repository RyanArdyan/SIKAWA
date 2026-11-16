<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'user_id',
        'tanggal',
        'check_in_lembur',
        'check_out_lembur',
        'photo_path_in',
        'photo_path_out',
        'keterangan_lembur',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'check_in_lembur' => 'datetime',
        'check_out_lembur' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menormalkan path foto di disk 'public' agar kompatibel dengan format lama
     * ('lemburs/in/...') maupun format baru ('namafile.jpeg').
     */
    public static function resolveStoragePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $cleanPath = ltrim($path, '/');

        if (str_starts_with($cleanPath, 'lemburs/')) {
            return $cleanPath;
        }

        if (Storage::disk('public')->exists('lemburs/' . $cleanPath)) {
            return 'lemburs/' . $cleanPath;
        }

        return $cleanPath;
    }

    public function getPhotoInUrlAttribute(): ?string
    {
        $resolved = self::resolveStoragePath($this->photo_path_in);

        return $resolved && Storage::disk('public')->exists($resolved)
            ? Storage::disk('public')->url($resolved)
            : null;
    }

    public function getPhotoOutUrlAttribute(): ?string
    {
        $resolved = self::resolveStoragePath($this->photo_path_out);

        return $resolved && Storage::disk('public')->exists($resolved)
            ? Storage::disk('public')->url($resolved)
            : null;
    }

    /**
     * Menghitung durasi lembur terformat (contoh: "2 jam 15 menit").
     */
    public function getDurasiFormatAttribute(): string
    {
        if (!$this->check_in_lembur || !$this->check_out_lembur) {
            return '-';
        }

        $totalMenit = (int) $this->check_in_lembur->diffInMinutes($this->check_out_lembur);
        $jam = intdiv($totalMenit, 60);
        $menit = $totalMenit % 60;

        if ($jam > 0 && $menit > 0) {
            return "{$jam} jam {$menit} menit";
        }

        return $jam > 0 ? "{$jam} jam" : "{$menit} menit";
    }
}
