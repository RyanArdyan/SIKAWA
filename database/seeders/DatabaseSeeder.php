<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\TimKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat atau Ambil Tim Kerja
        // Gunakan updateOrCreate agar data selalu sinkron tanpa duplikat
        $tim = TimKerja::updateOrCreate(
            ['nama' => 'Adum'],
            ['ketua_id' => null]
        );

        // 2. Buat Admin (User ID 1)
        User::updateOrCreate(
            ['email' => 'admin@bkk.com'],
            [
                'tim_kerja_id' => $tim->id,
                'name' => 'Admin BKK',
                'password' => bcrypt('password123'),
                'role' => 'super_admin',
                'nip' => '123456',
            ]
        );

        // // 3. Buat Pegawai (User ID 2) - PENTING: Supaya data absen punya pemilik
        // $pegawai = User::updateOrCreate(
        //     ['email' => 'pegawai@bkk.com'],
        //     [
        //         'tim_kerja_id' => $tim->id,
        //         'name' => 'Ardyan Pegawai',
        //         'password' => bcrypt('password123'),
        //         'role' => 'pegawai',
        //         'nip' => '123456789', // NIP yang nanti kamu tes di halaman riwayat
        //     ]
        // );

        // $userId = $pegawai->id; // Mengambil ID otomatis dari user kedua
        // $startDate = Carbon::now()->subDays(60);

        // $this->command->info('Memulai seeding data absen untuk '.$pegawai->name.'...');

        // for ($i = 0; $i <= 60; $i++) {
        //     $currentDate = (clone $startDate)->addDays($i);

        //     // Abaikan hari libur
        //     if ($currentDate->isWeekend()) {
        //         continue;
        //     }

        //     // Cek apakah data absen di tanggal tersebut sudah ada
        //     $exists = Attendance::where('user_id', $userId)
        //         ->whereDate('created_at', $currentDate)
        //         ->exists();

        //     if (! $exists) {
        //         // Jam masuk acak 07:00 - 08:30
        //         $checkIn = (clone $currentDate)->setTime(rand(7, 8), rand(0, 59));
        //         // Jam pulang acak 16:00 - 17:00
        //         $checkOut = (clone $currentDate)->setTime(rand(16, 17), rand(0, 59));

        //         Attendance::create([
        //             'user_id' => $userId,
        //             'photo_path' => 'dummy/masuk.jpg',
        //             'check_in_time' => $checkIn,
        //             'check_out_time' => $checkOut,
        //             'status' => $checkIn->format('H:i') > '08:00' ? 'terlambat' : 'hadir',
        //             'latitude_in' => '-0.02'.rand(100, 999),
        //             'longitude_in' => '109.34'.rand(100, 999),
        //             'created_at' => $currentDate,
        //             'updated_at' => $currentDate,
        //         ]);
        //     }
        // }

        // $this->command->info('Seeding selesai! Pegawai Dummy: '.$pegawai->nip);
    }
}
