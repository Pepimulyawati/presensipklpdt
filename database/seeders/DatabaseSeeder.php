<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Presensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT AKUN ADMIN
        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@pkl.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. BUAT AKUN GURU PEMBIMBING 1
        $userGuru1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budisantoso@pkl.com',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $guru1 = Guru::create([
            'user_id' => $userGuru1->id,
            'nama_guru' => 'Budi Santoso, S.Kom.',
            'nip' => '198801022015031002',
        ]);

        // 3. BUAT AKUN GURU PEMBIMBING 2
        $userGuru2 = User::create([
            'name' => 'Siti Sarah',
            'email' => 'sitisarah@pkl.com',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $guru2 = Guru::create([
            'user_id' => $userGuru2->id,
            'nama_guru' => 'Siti Sarah, M.T.',
            'nip' => '199005122018042001',
        ]);


        // 4. BUAT AKUN SISWA 1 (Bimbingan Guru 1)
        $userSiswa1 = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@siswa.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
        ]);

        $siswa1 = Siswa::create([
            'user_id' => $userSiswa1->id,
            'guru_id' => $guru1->id,
            'nama_lengkap' => 'Andi Wijaya',
            'nik_ktp' => '3201234567890001',
            'nisn' => '0061234561',
            'kelas' => 'XII RPL 1',
            'konsentrasi_keahlian' => 'Rekayasa Perangkat Lunak',
            'status_pkl' => 'aktif',
        ]);

        // BUAT AKUN SISWA 2 (Bimbingan Guru 1)
        $userSiswa2 = User::create([
            'name' => 'Bambang Pamungkas',
            'email' => 'bambang@siswa.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
        ]);

        $siswa2 = Siswa::create([
            'user_id' => $userSiswa2->id,
            'guru_id' => $guru1->id,
            'nama_lengkap' => 'Bambang Pamungkas',
            'nik_ktp' => '3201234567890002',
            'nisn' => '0061234562',
            'kelas' => 'XII RPL 1',
            'konsentrasi_keahlian' => 'Rekayasa Perangkat Lunak',
            'status_pkl' => 'aktif',
        ]);


        // 5. BUAT AKUN SISWA 3 (Bimbingan Guru 2)
        $userSiswa3 = User::create([
            'name' => 'Citra Lestari',
            'email' => 'citra@siswa.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa',
        ]);

        $siswa3 = Siswa::create([
            'user_id' => $userSiswa3->id,
            'guru_id' => $guru2->id,
            'nama_lengkap' => 'Citra Lestari',
            'nik_ktp' => '3201234567890003',
            'nisn' => '0061234563',
            'kelas' => 'XII TKJ 2',
            'konsentrasi_keahlian' => 'Teknik Komputer dan Jaringan',
            'status_pkl' => 'aktif',
        ]);


        // 6. GENERATE 10 DATA PRESENSI UNTUK MASING-MASING SISWA (Tanggal 1 - 10 Juni 2026)
        $daftarSiswaId = [$siswa1->id, $siswa2->id, $siswa3->id];

        foreach ($daftarSiswaId as $siswaId) {
            for ($hari = 1; $hari <= 10; $hari++) {
                $tanggal = sprintf('2026-06-%02d', $hari);
                
                // Hari ke-10 disimulasikan belum absen pulang (jam_pulang NULL)
                $isHariTerakhir = ($hari === 10);

                Presensi::create([
                    'siswa_id'         => $siswaId,
                    'tanggal'          => $tanggal,
                    'jam_masuk'        => sprintf('07:%02d:%02d', rand(15, 55), rand(10, 59)),
                    'jam_pulang'       => $isHariTerakhir ? null : sprintf('16:%02d:%02d', rand(0, 15), rand(10, 59)),
                    'latitude_masuk'   => '-6.917464',
                    'longitude_masuk'  => '107.619122',
                    'latitude_pulang'  => $isHariTerakhir ? null : '-6.917464',
                    'longitude_pulang' => $isHariTerakhir ? null : '107.619122',
                    'foto_masuk'       => null,
                    'foto_pulang'      => null,
                    'status'           => 'Hadir'
                ]);
            }
        }


        // 7. SETTING DEFAULT
        \DB::table('settings')->updateOrInsert(
            ['key' => 'simpan_foto_presensi'],
            ['value' => 'true', 'created_at' => now(), 'updated_at' => now()]
        );
    }
}