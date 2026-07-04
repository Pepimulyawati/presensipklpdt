<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Detail Guru
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_guru');
            $table->string('nip')->unique()->nullable();
            $table->timestamps();
        });

        // 2. Tabel Detail Siswa
   
        Schema::create('siswas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('guru_id')->nullable()->constrained('gurus')->onDelete('set null');
    $table->foreignId('dudi_id')->nullable()->constrained('dudis')->onDelete('set null');
    $table->foreignId('instruktur_id')->nullable()->constrained('instrukturs')->onDelete('set null');
    
    // KOLOM UTAMA
    $table->string('nis')->nullable(); // <-- Diizinkan null sesuai request Anda
    $table->string('nisn')->unique();  // Tetap dijadikan unique key untuk validasi utama
    $table->string('nama_lengkap');
    $table->string('nik_ktp')->nullable();
    $table->string('kelas');
    $table->string('konsentrasi_keahlian');
    $table->enum('status_pkl', ['belum', 'aktif', 'selesai'])->default('belum');
    $table->timestamps();
});

        // 3. Tabel Presensi
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('latitude_masuk')->nullable();
            $table->string('longitude_masuk')->nullable();
            $table->string('latitude_pulang')->nullable();
            $table->string('longitude_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alfa'])->default('Alfa');
            $table->timestamps();
        });

        // 4. Tabel Settings Global
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('presensis');
        Schema::dropIfExists('siswas');
        Schema::dropIfExists('gurus');
    }
};