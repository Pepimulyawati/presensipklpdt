<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'user_id',
        'nama_guru',
        'nip',
    ];

    // Hubungan Ke Tabel Akun Login (Users)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Hubungan Ke Tabel Siswa (Satu Guru membimbing banyak Siswa)
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'guru_id');
    }
}