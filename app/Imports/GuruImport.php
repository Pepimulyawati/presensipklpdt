<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Ambil data berdasarkan nama header di file Excel (harus huruf kecil semua / lowercase)
        $namaGuru = $row['nama_guru'] ?? null;
        $nip      = $row['nip'] ?? null;
        $email    = $row['email'] ?? null;
        $password = $row['password'] ?? 'password123'; // Default password jika di excel kosong

        // Skip jika ada kolom wajib yang kosong pada baris tersebut
        if (!$namaGuru || !$nip || !$email) {
            return null;
        }

        // Cek apakah email atau NIP sudah terdaftar di database
        $userExists = User::where('email', $email)->exists();
        $guruExists = Guru::where('nip', $nip)->exists();

        // Jika belum ada duplikat, lakukan insert berantai
        if (!$userExists && !$guruExists) {
            
            // 1. Buat user login
            $user = User::create([
                'name'     => $namaGuru,
                'email'    => $email,
                'password' => Hash::make($password),
                'role'     => 'guru',
            ]);

            // 2. Buat profil guru
            return new Guru([
                'user_id'   => $user->id,
                'nama_guru' => $namaGuru,
                'nip'       => $nip,
            ]);
        }

        return null;
    }
}