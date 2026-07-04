<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pengantaran extends Model
{
    protected $guarded = [];
    public function dudi() { return $this->belongsTo(Dudi::class); }
    public function guru() { return $this->belongsTo(User::class, 'guru_id'); }
}
