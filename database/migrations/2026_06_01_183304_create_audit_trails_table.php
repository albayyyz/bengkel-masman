<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('audit_trails', function (Blueprint $table) {
        $table->id();
        // Menghubungkan ke tabel users (siapa yang melakukan aksi)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('aksi'); // CREATE, UPDATE, atau DELETE
        $table->string('fitur'); // Transaksi, Suku Cadang, Jasa, dll.
        $table->text('deskripsi'); // Detail pesan: "Menghapus transaksi ID #12 milik Aldy"
        $table->string('ip_address')->nullable(); // Opsional: untuk memperkuat keamanan di skripsi
        $table->timestamps(); // Ini otomatis merekam waktu_kejadian via created_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
