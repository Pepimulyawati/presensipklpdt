<?php

namespace App\Exports;

use App\Models\Dudi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplatePemberangkatanExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * Mengambil seluruh data DUDI dari database untuk dijadikan baris template
     */
    public function collection()
    {
        return Dudi::all()->map(function ($dudi) {
            return [
                'nama_dudi'                        => $dudi->nama_dudi,
                'nama_guru'                        => '', // Dikosongkan agar diisi manual oleh admin
                'tanggal_pemberangkatan_yyyy_mm_dd'=> '', // Dikosongkan agar diisi manual oleh admin
                'status'                           => 'Selesai' // Nilai default sesuai permintaan
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
            'Tanggal Pemberangkatan (YYYY-MM-DD)',
            'Status'
        ];
    }

    /**
     * Styling tampilan Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Lebarkan kolom otomatis berdasarkan teks terpanjang (A sampai D)
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