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
        Schema::table('pengantarans', function (Blueprint $table) {
            // Mengubah tipe data menjadi string (varchar 255) dan mengizinkan null
            $table->string('tanggal_pengantaran')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengantarans', function (Blueprint $table) {
            // Kembalikan ke tipe data date jika migration di-rollback
            $table->date('tanggal_pengantaran')->nullable()->change();
        });
    }
};