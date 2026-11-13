<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    // 1. Menampilkan Semua Data Pelanggan
    public function index()
    {
        $pelanggan = Pelanggan::latest()->get();

        return view('pelanggan.index', compact('pelanggan'));
    }

    // 2. Menyimpan Data Pelanggan Baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        Pelanggan::create($validated);

        return redirect()->back()
            ->with('success', 'Data pelanggan berhasil ditambahkan!');
    }

    // 3. Menghapus Data Pelanggan
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        // Mencegah penghapusan pelanggan yang memiliki transaksi
        if ($pelanggan->transaksis()->exists()) {
            return redirect()->back()
                ->with('error', 'Data pelanggan tidak dapat dihapus karena masih memiliki riwayat transaksi.');
        }

        $pelanggan->delete();

        return redirect()->back()
            ->with('success', 'Data pelanggan berhasil dihapus!');
    }

    // 4. Menampilkan Halaman Edit Pelanggan
    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return view('pelanggan.edit', compact('pelanggan'));
    }

    // 5. Memperbarui Data Pelanggan
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui!');
    }
}
