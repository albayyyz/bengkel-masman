<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    // 1. Menampilkan Semua Data Barang & Jasa
    public function index()
    {
        $barang = Barang::latest()->get();
        return view('barang.index', compact('barang'));
    }

    // 2. Menyimpan Data Barang/Jasa Baru
    public function store(Request $request)
    {
        // PEMBERSIH otomatis: Jika ada input nominal dengan format titik (cth: 50.000), bersihkan jadi angka murni (50000)
        if ($request->has('harga_jual') && $request->harga_jual !== null) {
            $cleanedHarga = preg_replace('/[^0-9]/', '', $request->harga_jual);
            $request->merge(['harga_jual' => $cleanedHarga]);
        }

        // PERBAIKAN VALIDASI: Mengubah 'integer' menjadi 'numeric' agar lebih aman dan toleran
        $request->validate([
            'nama_barang' => 'required|string|max:100',
            'jenis'       => 'required|in:Barang,Jasa',
            'stok'        => 'required_if:jenis,Barang|nullable|integer|min:0',
            'harga_jual'  => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        // Jika inputnya Jasa, paksa stok menjadi 0 secara otomatis di backend
        if ($request->jenis === 'Jasa') {
            $data['stok'] = 0;
        }

        Barang::create($data);

        return redirect()->route('barang.index')->with('success', 'Data barang/jasa berhasil ditambahkan!');
    }

    // 3. Menampilkan Halaman Edit
    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        return view('barang.edit', compact('barang'));
    }

    // 4. Menyimpan Perubahan Data (Update)
    public function update(Request $request, $id)
    {
        // PEMBERSIH otomatis pada proses edit data
        if ($request->has('harga_jual') && $request->harga_jual !== null) {
            $cleanedHarga = preg_replace('/[^0-9]/', '', $request->harga_jual);
            $request->merge(['harga_jual' => $cleanedHarga]);
        }

        // PERBAIKAN VALIDASI: Mengubah 'integer' menjadi 'numeric'
        $request->validate([
            'nama_barang' => 'required|string|max:100',
            'jenis'       => 'required|in:Barang,Jasa',
            'stok'        => 'required_if:jenis,Barang|nullable|integer|min:0',
            'harga_jual'  => 'required|numeric|min:0',
        ]);

        $barang = Barang::findOrFail($id);
        $data = $request->all();

        if ($request->jenis === 'Jasa') {
            $data['stok'] = 0;
        }

        $barang->update($data);

        return redirect()->route('barang.index')->with('success', 'Data barang/jasa berhasil diperbarui!');
    }

    // 5. Menghapus Data Barang/Jasa
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return redirect()->back()->with('success', 'Data barang/jasa berhasil dihapus!');
    }
}