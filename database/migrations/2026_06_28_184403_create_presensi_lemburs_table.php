<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('presensi_lemburs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('latitude_masuk')->nullable();
            $table->string('longitude_masuk')->nullable();
            $table->string('latitude_pulang')->nullable();
            $table->string('longitude_pulang')->nullable();
            $table->longText('foto_masuk')->nullable();
            $table->longText('foto_pulang')->nullable();
            $table->integer('jumlah_menit')->nullable(); // Menyimpan total waktu lembur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_lemburs');
    }
};