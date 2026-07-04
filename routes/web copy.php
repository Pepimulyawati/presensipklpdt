<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\AdminGuruController;
use App\Http\Controllers\AdminSiswaController;
use App\Http\Controllers\AdminDudiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. Pengalihan Halaman Utama
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Grup Rute yang Membutuhkan Autentikasi (Login)
Route::middleware(['auth', 'verified'])->group(function () {

    // ==========================================
    // MANAGEMENT DATA MITRA DUDI
    // ==========================================
    // Gunting rute ganda! Sekarang hanya menembak importDudi
    Route::get('/admin/dudi/download-template', [AdminDudiController::class, 'downloadTemplateDudi'])->name('admin.dudi.template');
    Route::post('/admin/dudi/import', [AdminDudiController::class, 'importDudi'])->name('admin.dudi.import');
    Route::resource('/admin/dudi', AdminDudiController::class)->names([
        'index'   => 'admin.dudi.index',
        'store'   => 'admin.dudi.store',
        'update'  => 'admin.dudi.update',
        'destroy' => 'admin.dudi.destroy'
    ]);

    // ==========================================
    // MANAGEMENT DATA SISWA & PLOTTING (Pembersihan Tumpang Tindih)
    // ==========================================
    Route::get('/admin/siswa/master/download-template', [AdminSiswaController::class, 'downloadTemplateMaster'])->name('admin.siswa.master.template');
    Route::post('/admin/siswa/import', [AdminSiswaController::class, 'import'])->name('admin.siswa.import');
    
    Route::get('/admin/siswa/plotting/kelola', [AdminSiswaController::class, 'plotting'])->name('admin.siswa.plotting');
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

require __DIR__.'/auth.php';