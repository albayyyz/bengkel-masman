<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika tidak jamak (optional, untuk memastikan Laravel membaca tabel 'audit_trails')
    protected $table = 'audit_trails';

    // Daftarkan kolom yang boleh diisi secara massal
    protected $fillable = [
        'user_id',
        'aksi',
        'fitur',
        'deskripsi',
        'ip_address'
    ];

    /**
     * Relasi ke model User (Siapa pengguna yang melakukan aksi ini)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}