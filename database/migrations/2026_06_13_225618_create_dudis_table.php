<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       
    Schema::create('dudis', function (Blueprint $table) {
    $table->id();
    $table->string('nama_dudi');
    $table->text('alamat');
    $table->string('kontak');
    $table->string('zona'); // Contoh: Zona Dalam Kota, Luar Kota
    $table->enum('status_mou', ['aktif', 'tidak_aktif', 'proses']);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dudis');
    }
};
