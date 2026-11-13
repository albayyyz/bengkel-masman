<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'pelanggan_id',
        'total_bayar',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    /**
     * Relasi ke tabel Pelanggan (Satu transaksi dimiliki oleh satu pelanggan)
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Relasi ke tabel TransaksiDetail (Satu transaksi memiliki banyak detail barang/jasa)
     */
    public function details()
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_id');
    }
}