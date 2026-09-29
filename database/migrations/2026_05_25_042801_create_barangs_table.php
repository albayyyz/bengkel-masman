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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang', 100);
            // Kita pakai enum untuk membedakan item fisik vs jasa mekanik
            $table->enum('jenis', ['Barang', 'Jasa'])->default('Barang');
            $table->integer('stok')->default(0);
            $table->integer('harga_jual'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};