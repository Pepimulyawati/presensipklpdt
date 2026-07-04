<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dudi extends Model
{
    protected $fillable = ['nama_dudi', 'alamat', 'kontak', 'zona', 'status_mou'];

public function instrukturs() {
    return $this->hasMany(Instruktur::class);
}

public function pemberangkatan() {
    return $this->hasOne(Pemberangkatan::class, 'dudi_id');
}

public function pengantaran() { return $this->hasOne(Pengantaran::class); }
}


