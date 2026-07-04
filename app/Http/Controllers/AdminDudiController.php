<?php

namespace App\Http\Controllers;

use App\Models\Dudi;
use App\Models\Instruktur;
use App\Imports\DudiImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AdminDudiController extends Controller
{
    public function index(Request $request)
    {
        $query = Dudi::with('instrukturs');

        if ($request->filled('search')) {
            $query->where('nama_dudi', 'like', '%' . $request->search . '%')
                  ->orWhere('zona', 'like', '%' . $request->search . '%');
        }

        $dudis = $query->orderBy('nama_dudi', 'asc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Dudi/Index', [
            'dudis' => $dudis,
            'filters' => $request->only(['search'])
        ]);
    }

    // FITUR ANTI-BEBAN CPANEL: Mengambil sidik jari timestamp terakhir (Sangat Ringan)
    public function version()
    {
        $dudiMax = Dudi::max('updated_at') ?? '';
        $instrukturMax = Instruktur::max('updated_at') ?? '';
        return response()->json([
            'version' => md5($dudiMax . $instrukturMax)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_dudi'  => 'required|string|max:255',
            'alamat'     => 'required|string',
            'kontak'     => 'required|string',
            'zona'       => 'required|string',
            'status_mou' => 'required|in:aktif,tidak_aktif,proses',
            'instrukturs' => 'required|array|min:1',
            'instrukturs.*.nama_instruktur'   => 'required|string|max:255',
            'instrukturs.*.jabatan'           => 'required|string|max:255',
            'instrukturs.*.kontak_instruktur' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $dudi = Dudi::create($request->only(['nama_dudi', 'alamat', 'kontak', 'zona', 'status_mou']));
            
            foreach ($request->instrukturs as $ins) {
                $dudi->instrukturs()->create([
                    'nama_instruktur'   => $ins['nama_instruktur'],
                    'jabatan'           => $ins['jabatan'],
                    'kontak_instruktur' => $ins['kontak_instruktur'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Data DUDI & Instruktur Berhasil Ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $dudi = Dudi::findOrFail($id);

        $request->validate([
            'nama_dudi'  => 'required|string|max:255',
            'alamat'     => 'required|string',
            'kontak'     => 'required|string',
            'zona'       => 'required|string',
            'status_mou' => 'required|in:aktif,tidak_aktif,proses',
            'instrukturs' => 'required|array|min:1',
            'instrukturs.*.id'                => 'nullable|integer',
            'instrukturs.*.nama_instruktur'   => 'required|string|max:255',
            'instrukturs.*.jabatan'           => 'required|string|max:255',
            'instrukturs.*.kontak_instruktur' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $dudi) {
            // 1. Update Tabel Dudi
            $dudi->update($request->only(['nama_dudi', 'alamat', 'kontak', 'zona', 'status_mou']));

            // 2. Sinkronisasi Data Banyak Instruktur (Create / Update / Delete)
            $keepIds = [];
            foreach ($request->instrukturs as $ins) {
                if (!empty($ins['id'])) {
                    // Update yang sudah ada
                    $instruktur = Instruktur::where('dudi_id', $dudi->id)->find($ins['id']);
                    if ($instruktur) {
                        $instruktur->update([
                            'nama_instruktur'   => $ins['nama_instruktur'],
                            'jabatan'           => $ins['jabatan'],
                            'kontak_instruktur' => $ins['kontak_instruktur'],
                        ]);
                        $keepIds[] = $instruktur->id;
                    }
                } else {
                    // Buat baru jika instruktur ditambahkan saat edit
                    $newIns = $dudi->instrukturs()->create([
                        'nama_instruktur'   => $ins['nama_instruktur'],
                        'jabatan'           => $ins['jabatan'],
                        'kontak_instruktur' => $ins['kontak_instruktur'],
                    ]);
                    $keepIds[] = $newIns->id;
                }
            }

            // Hapus instruktur dari database jika di hapus di form UI
            $dudi->instrukturs()->whereNotIn('id', $keepIds)->delete();
        });

        return redirect()->back()->with('success', 'Data DUDI & Instruktur Berhasil Diperbarui!');
    }

    public function destroy($id)
    {
        $dudi = Dudi::findOrFail($id);
        $dudi->delete(); // Otomatis trigger cascade delete ke instrukturs via SQLite

        return redirect()->back()->with('success', 'Data DUDI Berhasil Dihapus!');
    }

    public function importDudi(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        try {
            Excel::import(new DudiImport, $request->file('file_excel'));
            return redirect()->back()->with('success', 'Data Mitra DUDI berhasil diimport!');
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file_excel' => 'Gagal mengimport: ' . $e->getMessage(),
            ]);
        }
    }
}

