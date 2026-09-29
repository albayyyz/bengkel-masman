<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    // Menentukan nama tabel yang ada di database
    protected $table = 'barangs';

    // Daftarkan kolom yang boleh diisi lewat form
    protected $fillable = [
        'nama_barang',
        'jenis',
        'stok',
        'harga_jual'
    ];
}