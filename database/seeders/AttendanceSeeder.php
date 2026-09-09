<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance; // sesuaikan jika nama model Anda berbeda
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil ID pengguna berdasarkan tim kerja
        $usersTim1 = User::where('tim_kerja_id', 1)->pluck('id')->toArray(); // Adum
        $usersTim2 = User::where('tim_kerja_id', 2)->pluck('id')->toArray(); // Tim Kerja 5
        $usersTim3 = User::where('tim_kerja_id', 3)->pluck('id')->toArray(); // Tim Kerja 2

        // Jika ada tim yang tidak memiliki user, ambil user acak agar seeder tidak error
        $allUsers = User::pluck('id')->toArray();
        if (empty($allUsers)) {
            $this->command->error('Tidak ada data user di database!');
            return;
        }

        // 2. Pembagian target 100 data: 34 untuk tim 1, 33 untuk tim 2, 33 untuk tim 3
        $distribution = [
            ['users' => !empty($usersTim1) ? $usersTim1 : $allUsers, 'count' => 34],
            ['users' => !empty($usersTim2) ? $usersTim2 : $allUsers, 'count' => 33],
            ['users' => !empty($usersTim3) ? $usersTim3 : $allUsers, 'count' => 33],
        ];

        $attendances = [];
        $startDate = Carbon::now()->subDays(40); // Rentang waktu absensi dalam 40 hari terakhir

        foreach ($distribution as $group) {
            $userPool = $group['users'];
            $targetCount = $group['count'];

            for ($i = 0; $i < $targetCount; $i++) {
                // Pilih user secara acak dari tim kerja tersebut
                $userId = $userPool[array_rand($userPool)];

                // Tentukan tanggal acak
                $date = (clone $startDate)->addDays(rand(0, 40));

                // Jam Masuk acak (antara 07:00:00 - 08:30:00)
                $checkInHour = rand(7, 8);
                $checkInMinute = rand(0, 59);
                $checkIn = (clone $date)->setTime($checkInHour, $checkInMinute, rand(0, 59));

                // Status hadir atau terlambat (terlambat jika lewat dari 08:00)
                $status = ($checkInHour > 8 || ($checkInHour == 8 && $checkInMinute > 0)) ? 'terlambat' : 'hadir';

                // Jam Keluar (antara 16:00:00 - 17:30:00)
                $checkOut = (clone $date)->setTime(rand(16, 17), rand(0, 59), rand(0, 59));

                // Tipe Absen acak ('WFO' atau 'WFA')
                $tipeAbsen = rand(0, 1) === 0 ? 'WFO' : 'WFA';

                $attendances[] = [
                    'user_id' => $userId,
                    'location_id' => 1, // Default location_id (bisa disesuaikan)
                    'photo_path' => 'absensi/in_' . rand(1, 5) . '.jpg',
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'photo_path_out' => 'absensi/out_' . rand(1, 5) . '.jpg',
                    'laporan_pdf' => 'laporan/report_' . rand(100, 999) . '.pdf',
                    'status' => $status,
                    'tipe_absen' => $tipeAbsen,
                    'reason_change_status' => $status === 'terlambat' ? 'Macet di jalan' : null,
                    'ip_address_log' => '127.0.0.1',
                    'latitude_in' => '-0.026330',
                    'longitude_in' => '109.342500',
                    'latitude_out' => '-0.026330',
                    'longitude_out' => '109.342500',
                    'created_at' => $checkIn,
                    'updated_at' => $checkOut,
                ];
            }
        }

        // 3. Insert massal ke database
        DB::table('attendances')->insert($attendances);

        $this->command->info('Berhasil membuat 100 data absensi seimbang untuk 3 tim kerja!');
    }
}
