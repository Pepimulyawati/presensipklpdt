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
        Schema::create('pemberangkatans', function (Blueprint $table) {
            $table->id();
            // Meniru relasi dudi dan guru dari pengantaran
            $table->foreignId('dudi_id')->constrained()->onDelete('cascade');
            $table->foreignId('guru_id')->nullable()->constrained('users')->onDelete('set null'); 
            
            // Langsung menggunakan tipe string & nullable seperti hasil akhir pengantaran
            $table->string('tanggal_pemberangkatan')->nullable(); 
            
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemberangkatans');
    }
};