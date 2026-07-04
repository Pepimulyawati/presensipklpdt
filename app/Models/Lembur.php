<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lembur extends Model
{
    // Ubah bagian ini agar sesuai dengan nama tabel di SQLite Anda
    protected $table = 'presensi_lemburs'; 

    protected $fillable = [
        'siswa_id', 'tanggal', 'jam_masuk', 'jam_pulang', 
        'latitude_masuk', 'longitude_masuk', 'latitude_pulang', 'longitude_pulang',
        'foto_masuk', 'foto_pulang', 'jumlah_menit'
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}