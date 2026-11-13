<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BarangController extends Controller
{
    // Menampilkan semua data barang dan jasa
    public function index()
    {
        $barang = Barang::latest()->get();

        return view('barang.index', compact('barang'));
    }

    // Menyimpan barang atau jasa baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:100',
            'jenis' => 'required|in:Barang,Jasa',
            'stok' => 'required_if:jenis,Barang|nullable|integer|min:0',
            'harga_jual' => 'required|integer|min:0',
        ]);

        if ($validated['jenis'] === 'Jasa') {
            $validated['stok'] = 0;
        }

        Barang::create($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang/jasa berhasil ditambahkan!');
    }

    // Menampilkan halaman edit
    public function edit($id)
    {
        $barang = Barang::findOrFail($id);

        return view('barang.edit', compact('barang'));
    }

    // Memperbarui data barang atau jasa
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:100',
            'jenis' => 'required|in:Barang,Jasa',
            'stok' => 'required_if:jenis,Barang|nullable|integer|min:0',
            'harga_jual' => 'required|integer|min:0',
        ]);

        $barang = Barang::findOrFail($id);

        // Periksa apakah barang sudah digunakan dalam transaksi
        $sudahDigunakan = TransaksiDetail::where(
            'barang_id',
            $barang->id
        )->exists();

        // Cegah perubahan jenis jika sudah memiliki riwayat transaksi
        if ($sudahDigunakan && $validated['jenis'] !== $barang->jenis) {
            throw ValidationException::withMessages([
                'jenis' => 'Jenis barang/jasa tidak dapat diubah karena sudah digunakan dalam riwayat transaksi.',
            ]);
        }

        // Stok jasa selalu nol
        if ($validated['jenis'] === 'Jasa') {
            $validated['stok'] = 0;
        }

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang/jasa berhasil diperbarui!');
    }

    // Menghapus barang atau jasa
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);

        $digunakanDalamTransaksi = TransaksiDetail::where(
            'barang_id',
            $barang->id
        )->exists();

        if ($digunakanDalamTransaksi) {
            return redirect()->back()->with(
                'error',
                "Barang/jasa '{$barang->nama_barang}' tidak dapat dihapus karena sudah digunakan dalam transaksi."
            );
        }

        $barang->delete();

        return redirect()->back()
            ->with('success', 'Data barang/jasa berhasil dihapus!');
    }
}
