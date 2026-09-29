<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit jika Laravel mendeteksinya sebagai jamak (plural)
    protected $table = 'transaksi_details';

    // Kolom yang wajib diisi saat perulangan foreach di Controller berjalan
    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'jumlah',
        'subtotal',
    ];

    /**
     * Relasi balik ke tabel Master Barang/Jasa
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relasi balik ke data induk Transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}