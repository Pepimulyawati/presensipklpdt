<?php

namespace App\Http\Controllers;

use App\Models\Pemberangkatan;
use App\Models\Dudi;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Imports\PemberangkatanImport; // Pastikan kelas import ini disesuaikan nanti jika ada
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplatePemberangkatanExport; // Pastikan kelas export ini disesuaikan nanti jika ada

class AdminPemberangkatanController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Pemberangkatan/Index', [
            // Eager load pemberangkatan.guru dan instrukturs untuk detail modal
            'dudiData' => fn() => Dudi::with(['pemberangkatan.guru', 'instrukturs'])
                ->when($request->search, fn($q, $s) => $q->where('nama_dudi', 'like', "%$s%")->orWhere('alamat', 'like', "%$s%"))
                ->latest()->paginate(10)->withQueryString(),
                
            'gurus' => fn() => User::where('role', 'guru')->select('id', 'name')->get(),
            
            'stats' => fn() => [
                'total'    => Dudi::count(),
                'selesai'  => Pemberangkatan::where('status', 'Selesai')->count(),
                'progress' => Pemberangkatan::where('status', 'Progress')->count(),
                'belum'    => Dudi::count() - Pemberangkatan::count(),
            ],
            
            'filters' => $request->only(['search'])
        ]);
    }

    public function version()
    {
        return response()->json(['version' => md5(Pemberangkatan::max('updated_at'))]);
    }

    public function store(Request $request)
    {
        Pemberangkatan::updateOrCreate(
            ['dudi_id' => $request->dudi_id],
            $request->validate([
                'guru_id' => 'nullable|exists:users,id',
                'tanggal_pemberangkatan' => 'nullable|string', // Sesuai dengan migrasi string
                'status' => 'required|in:Pending,Progress,Selesai,Tolak'
            ])
        );
        return back();
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new PemberangkatanImport, $request->file('file'));
            return back()->with('success', 'Data Jadwal Pemberangkatan berhasil diimport!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withErrors(['import' => 'Terjadi kesalahan saat membaca file. Pastikan format kolom benar.']);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new TemplatePemberangkatanExport, 
            'template_import_pemberangkatan.xlsx'
        );
    }
}