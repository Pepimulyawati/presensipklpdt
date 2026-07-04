<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $errors = [];
        $lineNumber = 1; // Baris 1 di Excel adalah Header

        // 1. MULAI TRANSAKSI DATABASE (Mengunci database agar aman dari kebocoran data)
        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $lineNumber++;

                // Ambil data kolom dan bersihkan spasi di ujung teks
                $namaLengkap         = isset($row['nama_lengkap']) ? trim($row['nama_lengkap']) : null;
                $email               = isset($row['email']) ? trim($row['email']) : null;
                $password            = isset($row['password']) ? trim($row['password']) : 'password123';
                $nisn                = isset($row['nisn']) ? trim($row['nisn']) : null;
                $nis                 = isset($row['nis']) ? trim($row['nis']) : null;
                $kelas               = isset($row['kelas']) ? trim($row['kelas']) : null;
                $konsentrasiKeahlian = isset($row['konsentrasi_keahlian']) ? trim($row['konsentrasi_keahlian']) : null;
                
                // Konversi NIP jika tidak sengaja terbaca sebagai format scientific / E+ di Excel
                $nipGuru = null;
                if (isset($row['nip_guru'])) {
                    $rawNip = trim(str_replace(["'", "`", " "], "", $row['nip_guru']));
                    if (str_contains(strtoupper($rawNip), 'E+')) {
                        // Paksa format scientific kembali menjadi string angka murni
                        $nipGuru = sprintf("%.0f", (float)$rawNip);
                    } else {
                        $nipGuru = $rawNip;
                    }
                }

                // Jika satu baris kosong total (tanpa data), abaikan aman tanpa memicu alarm
                if (!$namaLengkap && !$email && !$nisn && !$kelas) {
                    continue;
                }

                // 🚨 ALARM 1: VALIDASI DATA KOSONG PADA KOLOM WAJIB
                $kolomKosong = [];
                if (!$namaLengkap) $kolomKosong[] = 'nama_lengkap';
                if (!$email) $kolomKosong[] = 'email';
                if (!$nisn) $kolomKosong[] = 'nisn';
                if (!$kelas) $kolomKosong[] = 'kelas';
                if (!$konsentrasiKeahlian) $kolomKosong[] = 'konsentrasi_keahlian';
                if (!$nipGuru) $kolomKosong[] = 'nip_guru';

                if (!empty($kolomKosong)) {
                    $errors[] = "Baris {$lineNumber}: Kolom [" . implode(', ', $kolomKosong) . "] tidak boleh kosong.";
                    continue; // Catat error, lanjut periksa baris berikutnya
                }

                // 🚨 ALARM 2: VALIDASI DUPLIKAT EMAIL DI DATABASE
                $emailDuplikat = User::where('email', $email)->exists();
                if ($emailDuplikat) {
                    $errors[] = "Baris {$lineNumber} ({$namaLengkap}): Email '{$email}' sudah terdaftar di sistem.";
                    continue;
                }

                // 🚨 ALARM 3: VALIDASI DUPLIKAT NISN DI DATABASE
                $nisnDuplikat = Siswa::where('nisn', $nisn)->exists();
                if ($nisnDuplikat) {
                    $errors[] = "Baris {$lineNumber} ({$namaLengkap}): NISN '{$nisn}' sudah digunakan siswa lain.";
                    continue;
                }

                // 🚨 ALARM 4: VALIDASI KEBERADAAN GURU PEMBIMBING
                $guru = Guru::where('nip', $nipGuru)->first();
                if (!$guru) {
                    $errors[] = "Baris {$lineNumber} ({$namaLengkap}): Guru dengan NIP '{$nipGuru}' tidak ditemukan di database. Pastikan NIP benar atau import master guru terlebih dahulu.";
                    continue;
                }

                // JIKA LOLOS SEMUA VALIDASI ALARM, INPUT DATA KE DATABASE
                $user = User::create([
                    'name'     => $namaLengkap,
                    'email'    => $email,
                    'password' => Hash::make($password),
                    'role'     => 'siswa',
                ]);

                Siswa::create([
                    'user_id'              => $user->id,
                    'guru_id'              => $guru->id,
                    'dudi_id'              => null,
                    'instruktur_id'        => null,
                    'nis'                  => $nis, // Tersimpan NULL jika di Excel dikosongkan
                    'nisn'                 => $nisn,
                    'nama_lengkap'         => $namaLengkap,
                    'nik_ktp'              => null, // Diisi nanti oleh siswa lewat profile
                    'kelas'                => $kelas,
                    'konsentrasi_keahlian' => $konsentrasiKeahlian,
                    'status_pkl'           => 'belum',
                ]);
            }

            // 2. JIKA ADA ERROR YANG TERCATAT, GAGALKAN DAN BATALKAN SEMUA INPUT DATA
            if (!empty($errors)) {
                DB::rollBack(); // Menghapus kembali data baris sebelumnya yang sempat masuk
                throw new \Exception(implode("\n", $errors));
            }

            // 3. JIKA SELESAI TANPA ERROR, SIMPAN PERMANEN
            DB::commit();

        } catch (\Exception $e) {
            // Jika terjadi crash system tak terduga, pastikan database di-rollback
            DB::rollBack();
            throw $e;
        }
    }
}