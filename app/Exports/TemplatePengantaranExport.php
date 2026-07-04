<?php

namespace App\Exports;

use App\Models\Dudi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplatePengantaranExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * Mengambil seluruh data DUDI dari database untuk dijadikan baris template
     */
    public function collection()
    {
        // Mengambil semua data DUDI, lalu memetakan (map) strukturnya sesuai kolom template
        return Dudi::all()->map(function ($dudi) {
            return [
                'nama_dudi'                      => $dudi->nama_dudi,
                'nama_guru'                      => '', // Dikosongkan agar diisi manual oleh admin
                'tanggal_pengantaran_yyyy_mm_dd' => '', // Dikosongkan agar diisi manual oleh admin
                'status'                         => 'Selesai' // Nilai default sesuai permintaan Anda
            ];
        });
    }

    /**
     * Definisi Judul Kolom (Header) Excel
     */
    public function headings(): array
    {
        return [
            'Nama DUDI',
            'Nama Guru',
            'Tanggal Pengantaran (YYYY-MM-DD)',
            'Status'
        ];
    }

    /**
     * Styling tampilan Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Lebarkan kolom otomatis berdasarkan teks terpanjang
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Style untuk Header (Baris 1) menggunakan warna biru
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7']
                ]
            ]
        ];
    }
}