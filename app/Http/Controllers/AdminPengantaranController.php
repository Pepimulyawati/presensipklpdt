<?php

namespace App\Http\Controllers;

use App\Models\Pengantaran;
use App\Models\Dudi;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Imports\PengantaranImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplatePengantaranExport; // <-- Pastikan import ini ditambahkan

class AdminPengantaranController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Pengantaran/Index', [
            // Eager load instrukturs untuk detail modal, perbaiki pencarian
            'dudiData' => fn() => Dudi::with(['pengantaran.guru', 'instrukturs'])
                ->when($request->search, fn($q, $s) => $q->where('nama_dudi', 'like', "%$s%")->orWhere('alamat', 'like', "%$s%"))
                ->latest()->paginate(10)->withQueryString(),
                
            'gurus' => fn() => User::where('role', 'guru')->select('id', 'name')->get(),
            
            'stats' => fn() => [
                'total'    => Dudi::count(),
                'selesai'  => Pengantaran::where('status', 'Selesai')->count(),
                'progress' => Pengantaran::where('status', 'Progress')->count(),
                'belum'    => Dudi::count() - Pengantaran::count(),
            ],
            
            'filters' => $request->only(['search'])
        ]);
    }

    public function version()
    {
        return response()->json(['version' => md5(Pengantaran::max('updated_at'))]);
    }

    public function store(Request $request)
    {
        Pengantaran::updateOrCreate(
            ['dudi_id' => $request->dudi_id],
            $request->validate([
                'guru_id' => 'nullable|exists:users,id',
                'tanggal_pengantaran' => 'nullable|date',
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
            Excel::import(new PengantaranImport, $request->file('file'));
            return back()->with('success', 'Data Jadwal Pengantaran berhasil diimport!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withErrors(['import' => 'Terjadi kesalahan saat membaca file. Pastikan format kolom benar.']);
        }
    }

    public function downloadTemplate()
    {
        // Kode ini akan membuat Excel langsung di memori dan mendownloadnya seketika tanpa file fisik
        return Excel::download(
            new TemplatePengantaranExport, 
            'template_import_pengantaran.xlsx'
        );
    }
   
}