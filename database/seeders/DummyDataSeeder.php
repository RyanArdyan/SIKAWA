<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // ==========================================
        // 1. SEEDER TIM KERJA (4 Data)
        // ==========================================
        $timKerjaData = [
            ['nama' => 'Adum', 'ketua_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Pelayanan Kesehatan', 'ketua_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Karantina Kesehatan', 'ketua_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Upaya Kesehatan Lintas Wilayah', 'ketua_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Insert ke tabel tim_kerja mengabaikan jika sudah ada (karena field nama unique)
        DB::table('tim_kerja')->insertOrIgnore($timKerjaData);


        // ==========================================
        // 2. SEEDER USERS (50 Data Pegawai)
        // ==========================================
        $faker = Faker::create('id_ID');
        $usersData = [];

        // Gunakan password statis agar seeder berjalan cepat
        $defaultPassword = Hash::make('password123');

        // Ambil list ID tim kerja yang ada agar relasinya valid
        $timKerjaIds = DB::table('tim_kerja')->pluck('id')->toArray();

        for ($i = 0; $i < 50; $i++) {
            $gender = $faker->randomElement(['Laki-laki', 'Perempuan']);

            $usersData[] = [
                // Assign tim kerja secara acak, jika tabel kosong isi dengan null
                'tim_kerja_id' => !empty($timKerjaIds) ? $faker->randomElement($timKerjaIds) : null,
                'location_id' => null, // Dibuat null mengikuti struktur default database
                'nip' => $faker->unique()->numerify('19##########200#0#'), // Format NIP 18 digit
                'name' => $faker->name($gender === 'Laki-laki' ? 'male' : 'female'),
                'pangkat_golongan' => $faker->randomElement(['III/a', 'III/b', 'III/c', 'III/d', 'IV/a']),
                'jabatan' => $faker->randomElement(['Pranata Komputer Ahli Pertama', 'Analis SDM Aparatur', 'Perawat Mahir', 'Dokter Ahli Muda']),
                'kelas_jabatan' => (string) $faker->numberBetween(7, 12),
                'pendidikan' => $faker->randomElement(['D-III', 'S-1', 'S-2', 'Profesi']),
                'email' => $faker->unique()->safeEmail(),
                'role' => 'pegawai',
                'gender' => $gender,
                'password' => $defaultPassword,
                'email_verified_at' => $now,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert dalam format chunk (potongan) untuk mencegah limit memory jika data besar
        foreach (array_chunk($usersData, 25) as $chunk) {
            DB::table('users')->insert($chunk);
        }
    }
}
