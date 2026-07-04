<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\Siswa;
use App\Models\Guru; // Pastikan Model Guru di-import
use Illuminate\Http\Request;
use Inertia\Inertia;

class LemburBimbinganController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data Guru berdasarkan user_id yang sedang login (Sama persis seperti GuruController)
        $guru = Guru::where('user_id', auth()->id())->first();

        // Antisipasi jika data guru tidak ditemukan agar tidak crash
        if (!$guru) {
            return Inertia::render('Guru/LemburBimbingan', [
                'lembur' => ['data' => [], 'from' => 0, 'to' => 0, 'total' => 0, 'links' => []],
                'filters' => $request->only(['search', 'bulan']),
                'rekap' => ['Total Jam' => 0, 'Total Menit' => 0, 'Total Kegiatan' => 0]
            ]);
        }

        // 2. Ambil semua ID siswa yang dibimbing oleh guru ini
        $siswaIds = Siswa::where('guru_id', $guru->id)->pluck('id');

        // 3. Query data lembur dari tabel presensi_lemburs yang siswa_id-nya cocok
        $query = Lembur::with(['siswa'])->whereIn('siswa_id', $siswaIds);

        // Filter: Pencarian nama siswa (menggunakan kolom nama_lengkap sesuai tabel Anda)
        if ($request->filled('search')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        // Filter: Bulan
        if ($request->filled('bulan')) {
            $query->where('tanggal', 'like', $request->bulan . '%');
        }

        // 4. Hitung Rekap Jam dan Menit
        $totalMenit = (clone $query)->sum('jumlah_menit');
        $rekap = [
            'Total Jam' => round($totalMenit / 60, 1),
            'Total Menit' => $totalMenit,
            'Total Kegiatan' => $query->count()
        ];

        // 5. Pagination (15 data per halaman)
        $lemburData = $query->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Guru/LemburBimbingan', [
            'lembur' => $lemburData,
            'filters' => $request->only(['search', 'bulan']),
            'rekap' => $rekap
        ]);
    }
}