<?php

namespace App\Imports;

use App\Models\Pengantaran;
use App\Models\Dudi;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;

// Kita hapus WithHeadingRow, ganti dengan pembacaan indeks baris manual
class PengantaranImport implements ToModel
{
    private $isHeaderRow = true;

    public function model(array $row)
    {
        // 1. LEWATI BARIS PERTAMA (HEADER)
        // Agar tulisan "Nama DUDI", "Nama Guru" tidak ikut terimport ke database
        if ($this->isHeaderRow) {
            $this->isHeaderRow = false;
            return null;
        }

        // 2. AMBIL DATA BERDASARKAN URUTAN KOLOM EXCEL (Dimulai dari 0)
        // Kolom A (0) = Nama DUDI
        // Kolom B (1) = Nama Guru
        // Kolom C (2) = Tanggal Pengantaran
        // Kolom D (3) = Status
        
        $namaDudi       = isset($row[0]) ? trim($row[0]) : '';
        $namaGuru       = isset($row[1]) ? trim($row[1]) : '';
        $tanggalRaw     = isset($row[2]) ? trim($row[2]) : '';
        $statusRaw      = isset($row[3]) ? trim($row[3]) : 'Selesai';

        

        if (empty($namaDudi)) {
            return null; // Lewati jika baris Nama DUDI kosong
        }

        // 3. CARI DATA DUDI DI DATABASE
        $dudi = Dudi::where('nama_dudi', 'like', $namaDudi)->first();
        if (!$dudi) {
            return null; // Lewati jika nama DUDI tidak ditemukan di database
        }

        // 4. CARI DATA GURU DI DATABASE (Filter Role Guru)
        $guruId = null;
        if (!empty($namaGuru)) {
            $guru = User::where('role', 'guru')
                        ->where('name', 'like', "%{$namaGuru}%")
                        ->first();
            $guruId = $guru ? $guru->id : null;
        }

        // 5. PARSING TANGGAL (Mengatasi String biasa maupun Serial Number Excel)
     // 5. PARSING TANGGAL (Sangat Ketat & Multi-Format)

     // 5. PARSING TANGGAL (Fix untuk Serial Number Excel yang terbaca sebagai String)
      // 5. PARSING TANGGAL (Sekarang bebas karena tipe data database sudah string)
        $tanggal = !empty($tanggalRaw) ? trim($tanggalRaw) : null;

        // 6. VALIDASI STATUS
        $status = $statusRaw;
        if (!in_array($status, ['Pending', 'Progress', 'Selesai', 'Tolak'])) {
            $status = 'Selesai';
        }

        // 7. EKSEKUSI UPDATE ATAU CREATE
        return Pengantaran::updateOrCreate(
            ['dudi_id' => $dudi->id],
            [
                'guru_id'             => $guruId,
                'tanggal_pengantaran' => $tanggal,
                'status'              => $status,
            ]
        );
    }
}