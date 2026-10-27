<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class LemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Menggunakan faker dengan locale Indonesia
        $faker = Faker::create('id_ID');

        $lemburs = [];

        // 1. AMBIL ID USER SECARA DINAMIS DARI DATABASE
        // Menghindari error foreign key constraint jika ID 1-5 tidak berurutan/tidak ada
        $userIds = DB::table('users')->pluck('id')->toArray();

        // 2. PROTEKSI: Jika tabel users kosong, hentikan proses seeder
        if (empty($userIds)) {
            $this->command->error('Gagal: Tabel users kosong. Jalankan seeder users terlebih dahulu!');
            return;
        }

        // Pilihan status lembur yang ada di struktur database
        $statuses = ['pending', 'disetujui', 'ditolak'];

        // Looping untuk membuat 300 data
        for ($i = 0; $i < 300; $i++) {
            // Generate tanggal random dalam 6 bulan terakhir
            $tanggal = $faker->dateTimeBetween('-6 months', 'now');

            // Generate jam check_in lembur (misal antara jam 16:00 - 18:00)
            $checkIn = Carbon::instance($tanggal)->setTime(rand(16, 18), rand(0, 59), rand(0, 59));

            // Generate jam check_out lembur (misal antara jam 19:00 - 23:00 di hari yang sama)
            $checkOut = clone $checkIn;
            $checkOut->setTime(rand(19, 23), rand(0, 59), rand(0, 59));

            // Menentukan apakah data memiliki file atau tidak secara acak (opsional)
            $hasFiles = $faker->boolean(70); // 70% kemungkinan punya file

            $lemburs[] = [
                'user_id'           => $faker->randomElement($userIds), // Otomatis memilih ID yang valid
                'tanggal'           => $checkIn->format('Y-m-d'),
                'check_in_lembur'   => $checkIn->format('Y-m-d H:i:s'),
                'check_out_lembur'  => $checkOut->format('Y-m-d H:i:s'),
                'photo_path_in'     => $hasFiles ? 'lemburs/in/' . $faker->uuid . '.jpg' : null,
                'photo_path_out'    => $hasFiles ? 'lemburs/out/' . $faker->uuid . '.jpg' : null,
                'laporan_pdf'       => $hasFiles ? 'lemburs/' . $faker->uuid . '.pdf' : null,
                'keterangan_lembur' => $faker->sentence(6), // Kalimat acak untuk keterangan
                'status'            => $faker->randomElement($statuses),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        // Insert data ke database dalam bentuk chunk agar tidak memberatkan memori
        $chunks = array_chunk($lemburs, 100);
        foreach ($chunks as $chunk) {
            DB::table('lembur')->insert($chunk);
        }
    }
}
