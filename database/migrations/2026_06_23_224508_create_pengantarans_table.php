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
   Schema::create('pengantarans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dudi_id')->constrained()->onDelete('cascade');
    $table->foreignId('guru_id')->nullable()->constrained('users')->onDelete('set null'); // Tambahkan ->nullable()
    $table->date('tanggal_pengantaran')->nullable(); // Tambahkan ->nullable()
    $table->string('status');
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengantarans');
    }
};
