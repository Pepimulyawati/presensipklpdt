<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instruktur extends Model
{
    protected $fillable = ['dudi_id', 'nama_instruktur', 'jabatan', 'kontak_instruktur'];

public function dudi() {
    return $this->belongsTo(Dudi::class);
}
}
