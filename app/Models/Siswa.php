<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    // 1. Pastikan kolom dudi_id dan instruktur_id sudah masuk di fillable
   
    protected $fillable = [
    'user_id',
    'guru_id',
    'dudi_id',
    'instruktur_id',
    'nis', // <-- Tambahkan ini
    'nisn',
    'nama_lengkap',
    'nik_ktp',
    'kelas',
    'konsentrasi_keahlian',
    'status_pkl',
];

    // Relasi ke User (Sudah ada)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Guru Pembimbing (Sudah ada)
    public function guru()
    {
        return $this->belongsTo(Guru::class);
         return $this->belongsTo(User::class, 'guru_id')->where('role', 'guru');
    }

    // ========================================================
    // TAMBAHKAN DUA FUNGSI RELASI BARU INI DI BAWAH:
    // ========================================================

    /**
     * Relasi Siswa ke tempat PKL (DUDI)
     */
    public function dudi()
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    /**
     * Relasi Siswa ke Instruktur Lapangan
     */
    public function itsruktur() // Note: Jika di controller memanggil ->with('instruktur'), gunakan nama fungsi 'instruktur'
    {
        return $this->belongsTo(Instruktur::class, 'instruktur_id');
    }
    
    // Sesuaikan penulisan instruktur jika di controller menggunakan kata 'instruktur'
    public function bimbinganInstruktur()
    {
        return $this->belongsTo(Instruktur::class, 'instruktur_id');
    }
    
    // Agar aman dengan kode Controller sebelumnya, pakai nama ini:
    public function instruktur()
    {
        return $this->belongsTo(Instruktur::class, 'instruktur_id');
    }

    
}