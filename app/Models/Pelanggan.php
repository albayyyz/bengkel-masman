<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'pelanggans';

    // Mendaftarkan field yang boleh diisi secara massal (Mass Assignment)
    protected $fillable = [
        'nama_pelanggan',
        'no_telepon',
        'alamat'
    ];

    /**
     * Relasi ke tabel Transaksi (Satu pelanggan memiliki banyak transaksi)
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'pelanggan_id');
    }
}