<?php

namespace App\Imports;

use App\Models\Dudi;
use App\Models\Instruktur;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class DudiImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new Exception('File Excel kosong atau tidak memiliki data.');
        }

        $errors = [];
        $lineNumber = 1; // Baris 1 biasanya header jika menggunakan WithHeadingRow

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $lineNumber++;
                
                // 1. Sanitasi & Validasi Data DUDI
                $namaDudi = isset($row['nama_dudi']) ? trim($row['nama_dudi']) : null;
                
                if (empty($namaDudi)) {
                    // Lewati baris kosong, atau simpan sebagai error jika perlu
                    continue; 
                }

                $alamat    = $row['alamat'] ?? '-';
                $kontak    = $row['kontak'] ?? '-';
                $zona      = $row['zona'] ?? 'Dalam Kota';
                $statusMou = strtolower($row['status_mou'] ?? 'aktif');

                // 2. Simpan atau Ambil Data DUDI (Mencegah Duplikasi Nama)
                $dudi = Dudi::firstOrCreate(
                    ['nama_dudi' => $namaDudi],
                    [
                        'alamat'     => $alamat,
                        'kontak'     => $kontak,
                        'zona'       => $zona,
                        'status_mou' => $statusMou,
                    ]
                );

                // 3. Sanitasi Data Instruktur
                $namaInstruktur   = isset($row['nama_instruktur']) ? trim($row['nama_instruktur']) : null;
                $jabatan          = $row['jabatan'] ?? 'Staff';
                $kontakInstruktur = $row['kontak_instruktur'] ?? '-';

                // 4. Simpan Instruktur jika kolom nama_instruktur diisi
                if ($namaInstruktur) {
                    Instruktur::firstOrCreate(
                        [
                            'dudi_id'         => $dudi->id,
                            'nama_instruktur' => $namaInstruktur
                        ],
                        [
                            'jabatan'           => $jabatan,
                            'kontak_instruktur' => $kontakInstruktur,
                        ]
                    );
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // Kembalikan pesan error yang informatif
            throw new Exception("Error pada baris sekitar {$lineNumber}: " . $e->getMessage());
        }
    }
}