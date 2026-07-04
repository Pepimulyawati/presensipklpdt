<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

class AdminGuruController extends Controller
{
    /**
     * Tampilkan daftar guru dengan pencarian dan pagination.
     */
    public function index(Request $request): Response
    {
        $gurus = Guru::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_guru', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
            })
            ->orderBy('nama_guru', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Guru/Index', [
            'gurus' => $gurus,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Simpan data guru baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_guru' => 'required|string|max:255',
            'nip'       => 'required|string|max:50|unique:gurus,nip',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name'     => $validated['nama_guru'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => 'guru',
                ]);

                $user->guru()->create([
                    'nama_guru' => $validated['nama_guru'],
                    'nip'       => $validated['nip'],
                ]);
            });

            return redirect()->back()->with('message', 'Data Guru Berhasil Ditambahkan!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menambahkan data: ' . $e->getMessage()]);
        }
    }

    /**
     * Import Data Guru via CSV.
     * Tips: Untuk data sangat besar, disarankan menggunakan Laravel Excel (Queue).
     */
   
    /**
     * FEATURE: Import Data Guru Besar-besaran via CSV (Versi Auto-Detect Delimiter)
     */
   
    /**
     * FEATURE: Import Data Guru via Excel (.xlsx / .xls)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|file|mimes:xlsx,xls|max:2048', // Validasi file Excel asli
        ]);

        try {
            // Panggil class GuruImport untuk mengeksekusi file
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\GuruImport, $request->file('file_csv'));
            
            return redirect()->back()->with('message', 'Proses import data Excel selesai diproses!');
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Terjadi kesalahan saat membaca file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Update data guru.
     */
    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $validated = $request->validate([
            'nama_guru' => 'required|string|max:255',
            'nip'       => ['required', 'string', 'max:50', Rule::unique('gurus')->ignore($guru->id)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($guru->user_id)],
            'password'  => 'nullable|string|min:8',
        ]);

        try {
            DB::transaction(function () use ($validated, $guru) {
                $userData = [
                    'name'  => $validated['nama_guru'],
                    'email' => $validated['email']
                ];
                
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $guru->user()->update($userData);
                $guru->update([
                    'nama_guru' => $validated['nama_guru'],
                    'nip'       => $validated['nip'],
                ]);
            });

            return redirect()->back()->with('message', 'Data Guru Berhasil Diperbarui!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui data.']);
        }
    }

    /**
     * Hapus data guru.
     */
    public function destroy(Guru $guru): RedirectResponse
    {
        try {
            DB::transaction(function () use ($guru) {
                // Menghapus user akan otomatis menghapus guru jika menggunakan Cascade Delete di Database, 
                // jika tidak, hapus manual keduanya.
                $user = $guru->user;
                $guru->delete();
                $user?->delete();
            });

            return redirect()->back()->with('message', 'Data Guru Berhasil Dihapus!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus data.']);
        }
    }
}