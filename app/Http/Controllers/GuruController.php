<?php

namespace App\Http\Controllers; 

use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuruController extends Controller
{
    /**
     * Mengambil ID siswa-siswa yang dibimbing oleh guru yang sedang login
     * Melalui jembatan tabel 'gurus' terlebih dahulu.
     */
    private function getSiswaBimbinganIds($userId)
    {
        // 1. Cari profil guru berdasarkan user_id yang sedang login
        $profilGuru = Guru::where('user_id', $userId)->first();

        // 2. Jika profil guru tidak ditemukan, kembalikan array kosong agar tidak crash
        if (!$profilGuru) {
            return [];
        }

        // 3. Ambil daftar ID siswa yang kolom 'guru_id'-nya COCOK dengan ID Profil Guru tersebut
        return Siswa::where('guru_id', $profilGuru->id)->pluck('id')->toArray();
    }

   

    public function index(Request $request)
{
    $user = auth()->user();
    $siswaIds = $this->getSiswaBimbinganIds($user->id); // Ambil semua ID siswa bimbingan guru ini
    $totalSiswa = count($siswaIds);

    // 1. Query Dasar Presensi Siswa Bimbingan
    $query = Presensi::with('siswa.user')->whereIn('siswa_id', $siswaIds);

    // Filter Pencarian Nama Siswa
    if ($request->filled('search')) {
        $query->whereHas('siswa.user', function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    // Filter Bulan/Tahun (Format: YYYY-MM)
    if ($request->filled('bulan')) {
        $query->where('tanggal', 'like', $request->bulan . '%');
    }

    // Filter Status Presensi (Mendukung Multi-Select Array dari Vue)
    if ($request->filled('status')) {
        $statuses = is_array($request->status) ? $request->status : [$request->status];
        $query->whereIn('status', $statuses);
    }

    // Ambil data untuk Tabel Utama dengan Pagination
    $presensi = $query->orderBy('tanggal', 'desc')
                      ->paginate(15)
                      ->withQueryString();

    // 2. LOGIKA RANGKUMAN PERSENTASE (Untuk Card Atas)
    // Kita buat query terpisah khusus untuk menghitung rekap agar tidak terganggu limit pagination
    $queryRekap = Presensi::whereIn('siswa_id', $siswaIds);
    if ($request->filled('bulan')) {
        $queryRekap->where('tanggal', 'like', $request->bulan . '%');
    }
    
    $dataRekap = $queryRekap->get();
    $totalPresensiTerdata = $dataRekap->count();

    // Hitung persentase berdasarkan total data yang masuk pada filter tersebut
    // Jika tidak ada data sama sekali, set default 0 untuk menghindari pembagian dengan angka nol (division by zero)
    $rekap = [
        'Hadir' => $totalPresensiTerdata ? round(($dataRekap->where('status', 'Hadir')->count() / $totalPresensiTerdata) * 100) : 0,
        'Sakit' => $totalPresensiTerdata ? round(($dataRekap->where('status', 'Sakit')->count() / $totalPresensiTerdata) * 100) : 0,
        'Izin'  => $totalPresensiTerdata ? round(($dataRekap->where('status', 'Izin')->count() / $totalPresensiTerdata) * 100) : 0,
        'Alfa'  => $totalPresensiTerdata ? round(($dataRekap->where('status', 'Alfa')->count() / $totalPresensiTerdata) * 100) : 0,
    ];

    // 3. AMBIL DATA SISWA UNTUK DROPDOWN SEARCH (TAMBAH ABSEN)
    // Menggunakan list $siswaIds di atas agar hanya memuat siswa terdaftar/bimbingan guru ini saja
   
    // 3. AMBIL DATA SISWA UNTUK DROPDOWN SEARCH (TAMBAH ABSEN)
    // Ubah 'siswa.id' menjadi 'siswas.id' dan 'siswa.user_id' menjadi 'siswas.user_id'
    $siswas = Siswa::whereIn('siswas.id', $siswaIds)
        ->join('users', 'siswas.user_id', '=', 'users.id')
        ->select('siswas.id', 'users.name')
        ->orderBy('users.name', 'asc')
        ->get();

    // 4. Kirim Semua Data ke Frontend Vue
    return Inertia::render('Guru/SiswaBimbingan', [
        'presensi' => $presensi,
        'rekap'    => $rekap,
        'filters'  => $request->only(['search', 'bulan', 'status']),
        'siswas'   => $siswas // <-- Dikirim ke properti props di SiswaBimbingan.vue
    ]);
}


public function pivot(Request $request)
{
    $user = auth()->user();
    
    // Cari profil guru terlebih dahulu agar relasi penguncian tidak putus
    $profilGuru = Guru::where('user_id', $user->id)->first();

    if (!$profilGuru) {
        return Inertia::render('Guru/PivotPresensi', [
            'siswa' => [],
            'pivotData' => (object)[],
            'jumlahHari' => 30,
            'bulanDipilih' => date('Y-m'),
        ]);
    }

    // Ambil data semua siswa bimbingan beserta akun user-nya
    $siswaBimbingan = Siswa::with('user')->where('guru_id', $profilGuru->id)->get();
    $siswaIds = $siswaBimbingan->pluck('id')->toArray();

    // Mengambil filter bulan, default ke bulan berjalan saat ini (YYYY-MM)
    $bulanDipilih = $request->input('bulan', date('Y-m')); 
    
    // Pecah string filter bulan menjadi komponen Tahun dan Bulan terpisah
    $parsedDate = explode('-', $bulanDipilih);
    $tahun = $parsedDate[0];
    $bulan = $parsedDate[1];

    // Mengalkulasi jumlah total hari kerja kalender pada bulan terkait
    $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    // Tarik seluruh records presensi siswa terkait dalam range bulan terpilih
    $presensiData = Presensi::whereIn('siswa_id', $siswaIds)
        ->whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $bulan)
        ->get();

    // Map data presensi ke struktur matriks Pivot: [siswa_id][hari_ke]
    $pivotData = [];
    foreach ($presensiData as $p) {
        $hari = (int) date('d', strtotime($p->tanggal));
        $pivotData[$p->siswa_id][$hari] = [
            'status' => $p->status,
            'jam_masuk' => $p->jam_masuk,
            'jam_pulang' => $p->jam_pulang
        ];
    }

    return Inertia::render('Guru/PivotPresensi', [
        'siswa' => $siswaBimbingan,
        'pivotData' => (object)$pivotData, // Casting ke objek agar JavaScript membaca struktur map dengan benar
        'jumlahHari' => $jumlahHari,
        'bulanDipilih' => $bulanDipilih,
    ]);
}


    // AKSI CRUD: Update data absen anak yang salah kirim/input via Modal Dialog Guru
public function update(Request $request, $id)
{
    $request->validate([
        'status'     => 'required|in:Hadir,Izin,Sakit,Alfa',
        'jam_masuk'  => 'nullable',
        'jam_pulang' => 'nullable'
    ]);

    $presensi = Presensi::findOrFail($id);
    $presensi->update($request->only(['status', 'jam_masuk', 'jam_pulang']));

    return redirect()->back()->with('message', 'Data presensi berhasil diperbarui!');
}

// AKSI CRUD: Hapus baris absen anak yang salah input via tombol Trash Guru
public function destroy($id)
{
    $presensi = Presensi::findOrFail($id);
    $presensi->delete();

    return redirect()->back()->with('message', 'Data presensi berhasil dihapus!');
}

// AKSI CRUD: Tambah data absen anak secara manual
public function store(Request $request)
{
    // Validasi data yang masuk
    $request->validate([
        'siswa_id' => 'required|exists:siswas,id', // <-- Pastikan pakai 'siswas'
        'tanggal'  => 'required|date',
        'status'   => 'required|in:Hadir,Izin,Sakit,Alfa',
    ], [
        'siswa_id.required' => 'Siswa harus dipilih.',
        'siswa_id.exists'   => 'Siswa tidak ditemukan di database.',
        'tanggal.required'  => 'Tanggal presensi harus diisi.',
    ]);

    // Simpan ke database
    Presensi::create([
        'siswa_id' => $request->siswa_id,
        'tanggal'  => $request->tanggal,
        'status'   => $request->status,
        
        // Kolom lainnya (jam_masuk, foto, dll) tidak perlu ditulis di sini 
        // karena secara otomatis akan diisi NULL sesuai skema nullable() Anda.
    ]);

    return redirect()->back()->with('message', 'Data presensi berhasil ditambahkan secara manual!');
}

}