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

    // 2. Menyimpan Data Pelanggan Baru ke Database
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        Pelanggan::create($request->all());

        return redirect()->back()->with('success', 'Data pelanggan berhasil ditambahkan!');
    }

    // 3. Menghapus Data Pelanggan
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return redirect()->back()->with('success', 'Data pelanggan berhasil dihapus!');
    }

    // 4. Menampilkan Halaman Edit Pelanggan (Sudah Bersih dari Angka Typo)
    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    // 5. Menyimpan Perubahan Data Pelanggan
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->update($request->all());

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui!');
    }
}