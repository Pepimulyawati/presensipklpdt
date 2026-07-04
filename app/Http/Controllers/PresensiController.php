<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;


// use App\Models\Siswa;
// DONE ABSENSI

class PresensiController extends Controller
{

public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        
        // Ambil data presensi hari ini untuk logic disable tombol di Vue
        $presensiHariIni = Presensi::where('siswa_id', $siswa->id)
                            ->where('tanggal', date('Y-m-d'))
                            ->first();

        return Inertia::render('Siswa/Presensi', [
            'presensiHariIni' => $presensiHariIni
        ]);
    }

    public function store(Request $request)
    {
       $request->validate([
    'tipe'      => 'required|in:masuk,pulang',
    'status'    => 'required|in:Hadir,Izin,Sakit',
    'latitude'  => 'required', 
    'longitude' => 'required',
    'foto'      => 'required', 
]);

        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        // $tanggalHariIni = date('Y-m-d');
        // $jamSekarang = date('H:i:s');
        $tanggalHariIni = now()->format('Y-m-d');
$jamSekarang = now()->format('H:i:s');
        $namaFileFoto = null;

        // Logic simpan foto (Hanya jika ada foto yang dikirim)
        if ($request->foto) {
            $settingSimpanFoto = \DB::table('settings')->where('key', 'simpan_foto_presensi')->value('value');
            if ($settingSimpanFoto === 'true') {
                $image_parts = explode(";base64,", $request->foto);
                $image_base64 = base64_decode($image_parts[1]);
                $namaFileFoto = 'pkl_' . $request->tipe . '_' . $siswa->id . '_' . Str::random(10) . '.jpg';
                Storage::disk('public')->put('presensi/' . $namaFileFoto, $image_base64);
            }
        }

        $presensi = Presensi::where('siswa_id', $siswa->id)->where('tanggal', $tanggalHariIni)->first();

        if ($request->tipe === 'masuk') {
            if ($presensi) return redirect()->back()->with('error', 'Sudah absen masuk hari ini!');

            Presensi::create([
                'siswa_id' => $siswa->id,
                'tanggal' => $tanggalHariIni,
                'jam_masuk' => $jamSekarang,
                'latitude_masuk' => $request->latitude,
                'longitude_masuk' => $request->longitude,
                'foto_masuk' => $namaFileFoto,
                'status' => $request->status // Masukkan status (Hadir/Izin/Sakit)
            ]);

            return redirect()->route('siswa.dashboard')->with('message', 'Presensi MASUK berhasil!');
        } 
        
        if ($request->tipe === 'pulang') {
            if (!$presensi) return redirect()->back()->with('error', 'Belum absen masuk!');
            if ($presensi->jam_pulang) return redirect()->back()->with('error', 'Sudah absen pulang!');

            $presensi->update([
                'jam_pulang' => $jamSekarang,
                'latitude_pulang' => $request->latitude,
                'longitude_pulang' => $request->longitude,
                'foto_pulang' => $namaFileFoto,
            ]);

            return redirect()->route('siswa.dashboard')->with('message', 'Presensi PULANG berhasil!');
        }
    }

 

    //DONE PERBAIKAI TAMPILAN RIWAYAT
    public function riwayat(Request $request)
{
    // Cari id siswa
    $siswa = Siswa::where('user_id', Auth::id())->firstOrFail();
    
    // Inisialisasi Query Database
    $query = Presensi::where('siswa_id', $siswa->id);

    // Proses Filter Bulan & Tahun jika dikirim dari Vue
    if ($request->bulan) {
        $tahun = substr($request->bulan, 0, 4);
        $bulan = substr($request->bulan, 5, 2);
        $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }

    // Hitung Rangkuman Status (Berdasarkan filter saat ini)
    $summary = [
        'hadir' => (clone $query)->where('status', 'Hadir')->count(),
        'izin'  => (clone $query)->where('status', 'Izin')->count(),
        'sakit' => (clone $query)->where('status', 'Sakit')->count(),
    ];

    // Ambil data dengan Pagination (10 baris per halaman)
    $riwayat = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

    // Render ke halaman Vue
    return inertia('Siswa/Riwayat', [
        'riwayat' => $riwayat,
        'summary' => $summary,
        'filters' => $request->only('bulan')
    ]);
}
}