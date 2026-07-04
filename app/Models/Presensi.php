<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit (opsional tapi aman)
    protected $table = 'presensis';

    // Daftarkan kolom yang boleh diisi secara massal (Mass Assignment)
  
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'foto_masuk',
        'foto_pulang',
        'status'
    ];
    /**
     * Relasi balik ke Model Siswa
     * Hubungan: Setiap baris presensi dimiliki oleh satu siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}