<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'nama_pelanggan',
        'total_bayar',
    ];

    /**
     * Relasi ke tabel TransaksiDetail (Satu transaksi memiliki banyak detail barang/jasa)
     */
    public function details()
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_id');
    }
}