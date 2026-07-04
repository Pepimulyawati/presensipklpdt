<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemberangkatan extends Model
{
    use HasFactory;

    // Menentukan kolom mana saja yang boleh diisi secara massal
    protected $fillable = [
        'dudi_id',
        'guru_id',
        'tanggal_pemberangkatan',
        'status',
    ];

    /**
     * Relasi ke model Dudi (Satu data pemberangkatan dimiliki oleh satu DUDI)
     */
    public function dudi()
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    /**
     * Relasi ke model User (Mendapatkan data Guru/Petugas yang memploting)
     */
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}