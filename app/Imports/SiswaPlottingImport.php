<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Dudi;
use App\Models\Instruktur;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaPlottingImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $errors = [];
        $lineNumber = 1; // Baris 1 adalah header, data dimulai dari baris 2

        foreach ($rows as $row) {
            $lineNumber++;

            $nisn           = isset($row['nisn']) ? trim($row['nisn']) : null;
            $namaSiswa      = isset($row['nama_siswa_info_saja']) ? trim($row['nama_siswa_info_saja']) : 'Siswa';
            $namaDudi       = isset($row['nama_dudi']) ? trim($row['nama_dudi']) : null;
            $namaInstruktur = isset($row['nama_instruktur']) ? trim($row['nama_instruktur']) : null;

            // Jika baris kosong, skip tanpa eror
            if (!$nisn && !$namaDudi && !$namaInstruktur) {
                continue;
            }

            // 1. Validasi Keberadaan Siswa berdasarkan NISN
            $siswa = Siswa::where('nisn', $nisn)->first();
            if (!$siswa) {
                $errors[] = "Baris {$lineNumber}: Siswa dengan NISN '{$nisn}' tidak terdaftar di sistem.";
                continue; 
            }

            $dudiId = null;
            $instrukturId = null;

            // 2. Validasi Keberadaan DUDI (Jika kolom DUDI diisi di Excel)
            if ($namaDudi) {
                $dudi = Dudi::where('nama_dudi', $namaDudi)->first();
                if (!$dudi) {
                    $errors[] = "Baris {$lineNumber} ({$namaSiswa}): Industri '{$namaDudi}' tidak ditemukan di data Mitra DUDI. Mohon periksa salah ketik.";
                    continue;
                }
                $dudiId = $dudi->id;

                // 3. Validasi Keberadaan Instruktur di dalam DUDI tersebut
                if ($namaInstruktur) {
                    $instruktur = Instruktur::where('dudi_id', $dudiId)
                                            ->where('nama_instruktur', $namaInstruktur)
                                            ->first();
                    if (!$instruktur) {
                        $errors[] = "Baris {$lineNumber} ({$namaSiswa}): Instruktur '{$namaInstruktur}' tidak ditemukan bekerja di '{$namaDudi}'.";
                        continue;
                    }
                    $instrukturId = $instruktur->id;
                }
            }

            // Jika semua validasi teks lolos, lakukan update aman ke SQLite
            $siswa->update([
                'dudi_id' => $dudiId,
                'instruktur_id' => $instrukturId,
            ]);
        }

        // Jika selama perulangan ditemukan ada data yang salah ketik, batalkan semua dan lemparkan pesan eror ke layar
        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }
    }
}