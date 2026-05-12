<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\TimKerja;
use App\Models\User;
use App\Models\Setting;
use App\Models\Location;
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
        Location::create([
            'name'      => 'Kantor Induk',
            'latitude'  => -0.13281096611215687, // Contoh koordinat Pontianak
            'longitude' => 109.40759729246757,
            'radius'    => 500,
        ]);

        // -0.01940158279272492, 109.3340003789756
        Location::create([
            'name'      => 'Kantor Pelabuhan Dwikora',
            'latitude'  => -0.01940158279272492, // Contoh koordinat Pontianak
            'longitude' => 109.3340003789756,
            'radius'    => 500,
        ]);

        // -1.793972197385818, 109.95537323850277
        Location::create([
            'name'      => 'BKK Ketapang',
            'latitude'  => -1.793972197385818, // Contoh koordinat Pontianak
            'longitude' => 109.95537323850277,
            'radius'    => 500,
        ]);

        // 0.5053849531197078, 108.9172385961722
        Location::create([
            'name'      => 'BKK Kijing',
            'latitude'  => 0.5053849531197078, // Contoh koordinat Pontianak
            'longitude' => 108.9172385961722,
            'radius'    => 500,
        ]);

        // 1. Buat atau Ambil Tim Kerja
        $tim = TimKerja::updateOrCreate(
            ['nama' => 'Adum'],
            ['ketua_id' => null]
        );

        // 2. Buat Daftar User dalam Array agar bisa di-looping
        $users = [];

        // Admin BKK (NIP: 123456)
        $users[] = User::updateOrCreate(
            ['email' => 'admin@bkk.com'],
            [
                'tim_kerja_id' => $tim->id,
                'name' => 'Admin BKK',
                'password' => bcrypt('password123'),
                'role' => 'super_admin',
                'nip' => '123456',
            ]
        );

        // Ardyan Pegawai (NIP: 123456789)
        $users[] = User::updateOrCreate(
            ['email' => 'pegawai@bkk.com'],
            [
                'tim_kerja_id' => $tim->id,
                'name' => 'Ardyan Pegawai',
                'password' => bcrypt('password123'),
                'role' => 'pegawai',
                'nip' => '123456789',
            ]
        );

        // 3. Tambahkan Setting Jam Masuk jika belum ada
        Setting::updateOrCreate(
            ['key' => 'jam_masuk'],
            ['value' => '08:00']
        );

        $startDate = Carbon::now()->subDays(60);

        // 4. Looping untuk setiap User agar data seimbang
        foreach ($users as $user) {
            $this->command->info('Memulai seeding data absen untuk ' . $user->name . '...');

            for ($i = 0; $i <= 60; $i++) {
                $currentDate = (clone $startDate)->addDays($i);

                // Abaikan hari libur (Sabtu & Minggu)
                if ($currentDate->isWeekend()) {
                    continue;
                }

                // Cek apakah data absen untuk user ini di tanggal tersebut sudah ada
                $exists = Attendance::where('user_id', $user->id)
                    ->whereDate('created_at', $currentDate)
                    ->exists();

                if (!$exists) {
                    // Jam masuk acak 07:00 - 08:30
                    $checkIn = (clone $currentDate)->setTime(rand(7, 8), rand(0, 59));
                    // Jam pulang acak 16:00 - 17:00
                    $checkOut = (clone $currentDate)->setTime(rand(16, 17), rand(0, 59));

                    // Logika agar WFO dan WFA seimbang (bergantian setiap hari)
                    $tipeAbsen = ($i % 2 == 0) ? 'WFO' : 'WFA';

                    Attendance::create([
                        'user_id' => $user->id,
                        'photo_path' => 'dummy/masuk.jpg',
                        'check_in_time' => $checkIn,
                        'check_out_time' => $checkOut,
                        'status' => $checkIn->format('H:i') > '08:00' ? 'terlambat' : 'hadir',
                        'latitude_in' => '-0.02' . rand(100, 999),
                        'longitude_in' => '109.34' . rand(100, 999),
                        'tipe_absen' => $tipeAbsen,
                        'created_at' => $currentDate,
                        'updated_at' => $currentDate,
                    ]);
                }
            }
        }

        $this->command->info('Seeding selesai! Data User dan Tipe Absen (WFO/WFA) sekarang seimbang.');
    }
}
