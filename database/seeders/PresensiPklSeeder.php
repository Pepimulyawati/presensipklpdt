<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Presensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PresensiPklSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT DATA GURU PEMBIMBING (3 Orang)
        $dataGuru = [
            ['name' => 'Budi Santoso, S.Pd', 'email' => 'budi@guru.com', 'nip' => '19810102003041001'],
            ['name' => 'Siti Aminah, M.Kom', 'email' => 'siti@guru.com', 'nip' => '198505122010082002'],
            ['name' => 'Eko Prasetyo, S.T', 'email' => 'eko@guru.com', 'nip' => '199003252015031003'],
        ];

        $guruIds = [];
        foreach ($dataGuru as $g) {
            $userGuru = User::create([
                'name' => $g['name'],
                'email' => $g['email'],
                'password' => Hash::make('password123'),
                'role' => 'guru',
            ]);

            // MENYESUAIKAN: Mengisi kolom 'nama_guru' sesuai constraint NOT NULL database Anda
            $guruId = \DB::table('gurus')->insertGetId([
                'user_id' => $userGuru->id,
                'nip' => $g['nip'],
                'nama_guru' => $g['name'], // <--- Diisi ke nama_guru
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $guruIds[] = $guruId;
        }

        // 2. BUAT DATA SISWA (6 Orang)
        $dataSiswa = [
            ['name' => 'Andi Wijaya', 'email' => 'andi@siswa.com', 'nis' => '23001', 'guru_index' => 0],
            ['name' => 'Bagus Saputra', 'email' => 'bagus@siswa.com', 'nis' => '23002', 'guru_index' => 0],
            ['name' => 'Citra Lestari', 'email' => 'citra@siswa.com', 'nis' => '23003', 'guru_index' => 1],
            ['name' => 'Dina Mariana', 'email' => 'dina@siswa.com', 'nis' => '23004', 'guru_index' => 1],
            ['name' => 'Fajar Ramadhan', 'email' => 'fajar@siswa.com', 'nis' => '23005', 'guru_index' => 2],
            ['name' => 'Gilang Permana', 'email' => 'gilang@siswa.com', 'nis' => '23006', 'guru_index' => 2],
        ];

        foreach ($dataSiswa as $s) {
            $userSiswa = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password123'),
                'role' => 'siswa',
            ]);

            // MENYESUAIKAN: Mengisi 'nama_siswa' untuk mengantisipasi eror NOT NULL serupa pada tabel siswa
            $siswaId = \DB::table('siswas')->insertGetId([
                'user_id' => $userSiswa->id,
                'guru_id' => $guruIds[$s['guru_index']], 
                'nis' => $s['nis'],
                'nama_siswa' => $s['name'], // <--- Diisi ke nama_siswa jika kolom Anda bernama ini
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. GENERATE 10 DATA PRESENSI UNTUK MASING-MASING SISWA (Tanggal 1 - 10 Juni 2026)
            for ($hari = 1; $hari <= 10; $hari++) {
                $tanggal = sprintf('2026-06-%02d', $hari);
                $isHariTerakhir = ($hari === 10);

                Presensi::create([
                    'siswa_id' => $siswaId,
                    'tanggal' => $tanggal,
                    'jam_masuk' => sprintf('07:%02d:%02d', rand(15, 55), rand(10, 59)),
                    'jam_pulang' => $isHariTerakhir ? null : sprintf('16:%02d:%02d', rand(0, 15), rand(10, 59)),
                    'latitude_masuk' => '-6.917464',
                    'longitude_masuk' => '107.619122',
                    'latitude_pulang' => $isHariTerakhir ? null : '-6.917464',
                    'longitude_pulang' => $isHariTerakhir ? null : '107.619122',
                    'foto_masuk' => null,
                    'foto_pulang' => null,
                    'status' => 'Hadir'
                ]);
            }
        }
    }
}