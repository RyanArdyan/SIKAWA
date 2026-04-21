<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Signature('app:cleanup-old-photos')]
#[Description('Command description')]
class CleanupOldPhotos extends Command
{
    // Nama perintah yang akan dipanggil nanti
    protected $signature = 'photos:cleanup';
    protected $description = 'Menghapus foto absen dan datanya yang sudah lebih dari 3 hari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cari data yang usianya lebih dari 3 hari
        $oldAttendances = Attendance::where('created_at', '<', Carbon::now()->subDays(3))->get();

        if ($oldAttendances->isEmpty()) {
            $this->info('Tidak ada foto lama yang perlu dihapus.');
            return;
        }

        foreach ($oldAttendances as $attendance) {
            // 1. Hapus file fisik dari storage
            if (Storage::disk('public')->exists($attendance->photo_path)) {
                Storage::disk('public')->delete($attendance->photo_path);
            }

            // 2. Hapus record dari database
            $attendance->delete();
        }

        $this->info(count($oldAttendances) . ' data absen lama berhasil dibersihkan.');
    }
}
