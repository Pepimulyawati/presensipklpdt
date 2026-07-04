<?php

namespace App\Http\Controllers;

use App\Models\PresensiLembur;
use App\Models\Siswa; // <--- WAJIB TAMBAHKAN INI
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PresensiLemburController extends Controller
{
    public function index()
    {
        // 1. Cari data siswa berdasarkan user yang login
        $siswa = Siswa::where('user_id', Auth::id())->first();
        
        // Proteksi jika akun user belum punya data di tabel siswas
        if (!$siswa) {
            abort(403, 'Akun Anda belum memiliki data Siswa.');
        }

        $hariIni = now()->toDateString();

        // 2. Gunakan $siswa->id (ID dari tabel siswas)
        $lemburHariIni = PresensiLembur::where('siswa_id', $siswa->id)
            ->where('tanggal', $hariIni)
            ->first();

        return inertia('Siswa/PresensiLembur', [
            'lemburHariIni' => $lemburHariIni
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe'      => 'required|in:masuk,pulang',
            'latitude'  => 'required',
            'longitude' => 'required',
            'foto'      => 'required'
        ]);

        // 1. Cari data siswa berdasarkan user yang login
        $siswa = Siswa::where('user_id', Auth::id())->first();
        
        if (!$siswa) {
            abort(403, 'Akun Anda belum memiliki data Siswa.');
        }

        $hariIni = Carbon::today()->toDateString();
        $waktuSekarang = Carbon::now();

        // 2. Gunakan $siswa->id di sini
        $lembur = PresensiLembur::firstOrNew([
            'siswa_id' => $siswa->id, 
            'tanggal'  => $hariIni,
        ]);

        if ($request->tipe === 'masuk') {
            abort_if($lembur->jam_masuk, 400, 'Anda sudah melakukan presensi masuk lembur hari ini.');
            
            $lembur->fill([
                'jam_masuk'       => $waktuSekarang->format('H:i:s'),
                'latitude_masuk'  => $request->latitude,
                'longitude_masuk' => $request->longitude,
                'foto_masuk'      => $request->foto,
            ]);
        } else {
            abort_if(!$lembur->jam_masuk || $lembur->jam_pulang, 400, 'Akses presensi pulang tidak valid.');

            $jamMasuk = Carbon::parse($lembur->jam_masuk);
            $selisihMenit = $jamMasuk->diffInMinutes($waktuSekarang);

            $lembur->fill([
                'jam_pulang'       => $waktuSekarang->format('H:i:s'),
                'latitude_pulang'  => $request->latitude,
                'longitude_pulang' => $request->longitude,
                'foto_pulang'      => $request->foto,
                'jumlah_menit'     => $selisihMenit,
            ]);
        }

        $lembur->save();

        return back()->with('success', 'Presensi lembur berhasil disimpan.');
    }


    // DONE  METHOD RIWAYAT LEMBUR SESUAI

    public function riwayat(Request $request)
{
    // 1. Cari data siswa berdasarkan user login
    $siswa = Siswa::where('user_id', Auth::id())->firstOrFail();
    
    // 2. Query data lembur siswa tersebut
    $query = PresensiLembur::where('siswa_id', $siswa->id);

    // 3. Proses Filter Bulan jika ada
    if ($request->bulan) {
        $tahun = substr($request->bulan, 0, 4);
        $bulan = substr($request->bulan, 5, 2);
        $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }

    // 4. Hitung Rangkuman (Hanya yang sudah absen pulang / memiliki jumlah_menit)
    $totalMenit = (clone $query)->whereNotNull('jam_pulang')->sum('jumlah_menit');
    $totalHari = (clone $query)->whereNotNull('jam_pulang')->count();

    // Konversi total menit ke format Jam dan Menit
    $jam = floor($totalMenit / 60);
    $menit = $totalMenit % 60;

    $summary = [
        'total_hari'   => $totalHari,
        'total_durasi' => "{$jam} Jam {$menit} Menit"
    ];

    // 5. Ambil data dengan Pagination (10 data per halaman)
    $riwayat = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

    // 6. Kirim ke FE RiwayatLembur.vue
    return inertia('Siswa/RiwayatLembur', [
        'riwayat' => $riwayat,
        'summary' => $summary,
        'filters' => $request->only('bulan')
    ]);
}
}