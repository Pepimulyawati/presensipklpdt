<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LemburBimbinganController; // <-- PASTIKAN BARIS INI ADA
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\AdminGuruController;
use App\Http\Controllers\AdminSiswaController;
use App\Http\Controllers\AdminDudiController;
use App\Http\Controllers\AdminPengantaranController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\PresensiLemburController;
use App\Http\Controllers\AdminPemberangkatanController;
use App\Http\Controllers\Admin\SiswaController; // Sesuaikan dengan folder tempat Anda menyimpan controller ini

// ==========================================
// 1. HALAMAN UTAMA (Dilengkapi Props agar Welcome.vue tidak error)
// ==========================================
// Route baru: Otomatis redirect ke halaman login
Route::redirect('/', '/login');

// ==========================================
// 2. GRUP RUTE YANG MEMBUTUHKAN AUTENTIKASI
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/guru/lembur', [LemburBimbinganController::class, 'index'])->name('guru.lembur');

// Route untuk Presensi Utama & Riwayatnya
    Route::get('/siswa/presensi', [PresensiController::class, 'index'])->name('siswa.presensi');
    Route::get('/siswa/riwayat', [PresensiController::class, 'riwayat'])->name('siswa.riwayat');

    // Route untuk Presensi Lembur & Riwayatnya
    Route::get('/siswa/lembur', [PresensiLemburController::class, 'index'])->name('siswa.lembur');
    Route::get('/siswa/riwayat-lembur', [PresensiLemburController::class, 'riwayat'])->name('siswa.riwayat-lembur');

Route::get('/siswa/lembur', [PresensiLemburController::class, 'index'])->name('siswa.lembur');
    
    // Route untuk memproses data presensi lembur
    Route::post('/siswa/lembur', [PresensiLemburController::class, 'store'])->name('siswa.lembur.store');


Route::post('/guru/presensi', [GuruController::class, 'store'])->name('guru.presensi.store');

// Pastikan ini berada di dalam group middleware guru milik Anda
Route::put('/guru/presensi/{id}', [GuruController::class, 'update'])->name('guru.presensi.update');
Route::delete('/guru/presensi/{id}', [GuruController::class, 'destroy'])->name('guru.presensi.destroy');


Route::get('/admin/siswa', [AdminSiswaController::class, 'index'])->name('admin.siswa.index');
Route::put('/admin/siswa/{id}/update-guru', [AdminSiswaController::class, 'updateGuruInline'])->name('admin.siswa.update-guru');
// Route::put('/admin/siswa/{siswa}/update-guru', [SiswaController::class, 'updateGuruInline'])->name('admin.siswa.update-guru');
// Route::put('/admin/siswa/{id}/update-guru', [SiswaController::class, 'updateGuruInline'])->name('admin.siswa.update-guru');
// Route::put('/admin/siswa/{id}/update-guru', [App\Http\Controllers\Admin\SiswaController::class, 'updateGuruInline'])->name('admin.siswa.update-guru');
// ==========================================
    // JEMBATAN OTOMATIS ROUTE 'dashboard' (Fix Ziggy Error Welcome.vue)
    // ==========================================
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'guru') {
            return redirect()->route('guru.dashboard');
        } elseif ($user->role === 'siswa') {
            return redirect()->route('siswa.dashboard');
        }
        
        return abort(403, 'Role tidak dikenali.');
    })->name('dashboard'); // <-- Nama ini wajib ada untuk memuaskan Welcome.vue




    Route::get('/admin/pemberangkatan/download-template', [AdminPemberangkatanController::class, 'downloadTemplate'])->name('admin.pemberangkatan.download-template');
Route::post('/admin/pemberangkatan/import', [AdminPemberangkatanController::class, 'importExcel'])->name('admin.pemberangkatan.import');
Route::get('/admin/pemberangkatan/version', [AdminPemberangkatanController::class, 'version'])->name('admin.pemberangkatan.version');
Route::get('/admin/pemberangkatan', [AdminPemberangkatanController::class, 'index'])->name('admin.pemberangkatan.index');
Route::post('/admin/pemberangkatan', [AdminPemberangkatanController::class, 'store'])->name('admin.pemberangkatan.store');
    

    // ==========================================
    // MANAGEMENT JADWAL PENGANTARAN (Sudah Diberi Prefix /admin)
    // ==========================================
    Route::get('/admin/pengantaran/download-template', [AdminPengantaranController::class, 'downloadTemplate'])->name('admin.pengantaran.download-template');
    Route::post('/admin/pengantaran/import', [AdminPengantaranController::class, 'importExcel'])->name('admin.pengantaran.import');
    Route::get('/admin/pengantaran/version', [AdminPengantaranController::class, 'version'])->name('admin.pengantaran.version');
    Route::get('/admin/pengantaran', [AdminPengantaranController::class, 'index'])->name('admin.pengantaran.index');
    Route::post('/admin/pengantaran', [AdminPengantaranController::class, 'store'])->name('admin.pengantaran.store');

    // ==========================================
    // MANAGEMENT DATA MITRA DUDI
    // ==========================================
    Route::get('/admin/dudi/download-template', [AdminDudiController::class, 'downloadTemplateDudi'])->name('admin.dudi.template');
    Route::post('/admin/dudi/import', [AdminDudiController::class, 'importDudi'])->name('admin.dudi.import');
    
    Route::resource('/admin/dudi', AdminDudiController::class)->names([
        'index'   => 'admin.dudi.index',
        'store'   => 'admin.dudi.store',
        'update'  => 'admin.dudi.update',
        'destroy' => 'admin.dudi.destroy'
    ]);

    // ==========================================
    // MANAGEMENT DATA SISWA & PLOTTING
    // ==========================================
    Route::get('/admin/siswa/master/download-template', [AdminSiswaController::class, 'downloadTemplateMaster'])->name('admin.siswa.master.template');
    Route::post('/admin/siswa/import', [AdminSiswaController::class, 'import'])->name('admin.siswa.import');
    
    Route::get('/admin/siswa/plotting', [AdminSiswaController::class, 'plotting'])->name('admin.siswa.plotting');
    Route::get('/admin/siswa/plotting/download-template', [AdminSiswaController::class, 'downloadTemplatePlotting'])->name('admin.siswa.plotting.template');
    Route::post('/admin/siswa/plotting/import', [AdminSiswaController::class, 'importPlotting'])->name('admin.siswa.plotting.import');
    Route::put('/admin/siswa/plotting/{id}', [AdminSiswaController::class, 'updatePlotting'])->name('admin.siswa.update-plotting');

    Route::resource('/admin/siswa', AdminSiswaController::class)->names([
        'index'   => 'admin.siswa.index',
        'store'   => 'admin.siswa.store',
        'update'  => 'admin.siswa.update',
        'destroy' => 'admin.siswa.destroy',
    ]);

    // ==========================================
    // MANAGEMENT DATA GURU
    // ==========================================
    Route::post('/admin/guru/import', [AdminGuruController::class, 'import'])->name('admin.guru.import');
    Route::resource('/admin/guru', AdminGuruController::class)->names([
        'index'   => 'admin.guru.index',
        'store'   => 'admin.guru.store',
        'update'  => 'admin.guru.update',
        'destroy' => 'admin.guru.destroy',
    ]);

    // ==========================================
    // ROUTE ROLE DASHBOARD & KINERJA
    // ==========================================
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    // Guru Dashboard
    Route::get('/guru/dashboard', [GuruController::class, 'index'])->name('guru.dashboard');
    Route::get('/guru/rekap-pivot', [GuruController::class, 'pivot'])->name('guru.pivot');

    // Siswa Dashboard & Absensi
    Route::get('/siswa/dashboard', function () {
        return Inertia::render('Siswa/Dashboard');
    })->name('siswa.dashboard');
    Route::get('/siswa/presensi', [PresensiController::class, 'index'])->name('siswa.presensi');
    Route::post('/siswa/presensi', [PresensiController::class, 'store'])->name('siswa.presensi.store');
    Route::get('/siswa/riwayat', [PresensiController::class, 'riwayat'])->name('siswa.riwayat');

    // ==========================================
    // PROFILE BREEZE BUNDLE
    // ==========================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route Real-time Version Check 
Route::get('/dudi/version', [AdminDudiController::class, 'version']);

require __DIR__.'/auth.php';