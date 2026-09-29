<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Barang;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    /**
     * Menampilkan halaman form kasir baru.
     */
    public function create()
    {
        $barangs = Barang::all(); 
        return view('transaksi.create', compact('barangs'));
    }

   /**
     * Menampilkan daftar riwayat transaksi beserta filter rentang tanggal dinamis.
     */
    public function index(Request $request)
    {
        // 1. Tangkap parameter filter tanggal dari request (jika ada)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 2. Query dasar untuk Tabel Riwayat & Card Utama (Gunakan penanda filter jika diisi)
        $queryTransaksi = Transaksi::with('details.barang');
        $queryDetail = TransaksiDetail::query();

        if ($startDate && $endDate) {
            $queryTransaksi->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
            $queryDetail->whereHas('transaksi', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            });
        }

        // Eksekusi data untuk tabel riwayat transaksi
        $transaksis = $queryTransaksi->latest()->get();
        $barangs = Barang::all(); 

        // 3. Hitung total pendapatan khusus "Barang" (Suku Cadang) sesuai filter
        $totalSparepart = (clone $queryDetail)->whereHas('barang', function($query) {
            $query->where(DB::raw('LOWER(jenis)'), 'barang');
        })->sum('subtotal');

        // 4. Hitung total pendapatan khusus "Jasa" (Servis) sesuai filter
        $totalJasa = (clone $queryDetail)->whereHas('barang', function($query) {
            $query->where(DB::raw('LOWER(jenis)'), 'jasa');
        })->sum('subtotal');

        // 5. Hitung akumulasi omset keseluruhan sesuai filter
        $grandTotalOmset = $startDate && $endDate 
            ? Transaksi::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('total_bayar')
            : Transaksi::sum('total_bayar');


        // 6. === LOGIC DATA GRAFIK BULANAN / HARIAN ===
        // Supaya grafik fleksibel, jika filter di bawah 31 hari kita tampilkan per HARI, jika lebih kita tampilkan per BULAN
        $labels = [];
        $dataOmset = [];

        if ($startDate && $endDate) {
            // Jika user memfilter rentang tanggal tertentu
            $period = new \DatePeriod(
                new \DateTime($startDate),
                new \DateInterval('P1D'),
                (new \DateTime($endDate))->modify('+1 day')
            );

            // Inisialisasi array kosong sesuai rentang tanggal yang dipilih
            $analitikData = [];
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $analitikData[$tglStr] = 0;
                $labels[] = $date->format('d M');
            }

            // Ambil data transaksi di rentang tersebut, kelompokkan per hari
            $rawChartData = Transaksi::select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(total_bayar) as total'))
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get();

            foreach ($rawChartData as $row) {
                if (isset($analitikData[$row->tanggal])) {
                    $analitikData[$row->tanggal] = (int) $row->total;
                }
            }
            $dataOmset = array_values($analitikData);

        } else {
            // Default: Jika tidak ada filter, tampilkan 12 Bulan di tahun berjalan
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $analitikBulanan = array_fill(1, 12, 0);

            $rawChartData = Transaksi::select(DB::raw('MONTH(created_at) as bulan'), DB::raw('SUM(total_bayar) as total'))
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get();

            foreach ($rawChartData as $row) {
                $analitikBulanan[$row->bulan] = (int) $row->total;
            }
            $dataOmset = array_values($analitikBulanan);
        }

        // === SUDAH DIPERBAIKI: Mengambil 10 log audit trail terbaru beserta data user ===
        $auditTrails = AuditTrail::with('user')->latest()->take(10)->get();

        return view('transaksi.index', compact(
            'transaksis', 
            'barangs', 
            'totalSparepart', 
            'totalJasa', 
            'grandTotalOmset',
            'startDate',
            'endDate',
            'auditTrails' // Menyisipkan data log audit ke view halaman riwayat
        ))->with([
            'chartLabels' => $labels,
            'chartData' => $dataOmset
        ]);
    }

    /**
     * Menyimpan data transaksi baru ke database (Dengan Validasi Batas Stok Aman).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // === VALIDASI STOK SEBELUM MENYIMPAN (Anti Typo Huruf Kapital) ===
            foreach ($request->items as $item) {
                $barang = Barang::find($item['barang_id']);
                
                if ($barang) {
                    // Konversi jenis ke huruf kecil (mencegah eror akibat perbedaan tulisan Barang/barang)
                    $jenisBarang = strtolower($barang->jenis);

                    if ($jenisBarang === 'barang') {
                        if ($barang->stok < $item['jumlah']) {
                            DB::rollBack();
                            return redirect()->back()
                                ->withInput()
                                ->with('error', "Gagal Transaksi! Stok suku cadang '{$barang->nama_barang}' tidak mencukupi. (Sisa Stok: {$barang->stok}, Diminta: {$item['jumlah']})");
                        }
                    }
                }
            }

            // Jika lolos validasi stok, hitung nominal grand total belanja
            $totalBayar = $request->total_bayar ?? array_sum(array_column($request->items, 'subtotal'));

            $transaksi = Transaksi::create([
                'nama_pelanggan' => $request->nama_pelanggan,
                'total_bayar' => $totalBayar,
            ]);

            // Simpan Detail Item & Potong Stok
            foreach ($request->items as $item) {
                $barang = Barang::find($item['barang_id']);
                $subtotal = $barang->harga_jual * $item['jumlah'];

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id'    => $barang->id,
                    'jumlah'       => $item['jumlah'],
                    'subtotal'     => $subtotal
                ]);

                if ($barang && strtolower($barang->jenis) === 'barang') {
                    $barang->stok -= $item['jumlah'];
                    $barang->save();
                }
            }

            // === TRIGGER AUDIT TRAIL: CATAT LOG TRANSAKSI BARU ===
            AuditTrail::create([
                'user_id' => auth()->id(),
                'aksi' => 'CREATE',
                'fitur' => 'Transaksi',
                'deskripsi' => 'Membuat transaksi baru untuk pelanggan ' . $transaksi->nama_pelanggan . ' dengan total bayar Rp ' . number_format($transaksi->total_bayar, 0, ',', '.'),
                'ip_address' => $request->ip()
            ]);

            DB::commit();
            return redirect()->back()
                ->with('success', 'Transaksi atas nama ' . $request->nama_pelanggan . ' berhasil disimpan!')
                ->with('nama_pelanggan', $request->nama_pelanggan);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan Form Edit Transaksi.
     */
    public function edit($id)
    {
        $transaksi = Transaksi::with('details.barang')->findOrFail($id);
        $barangs = Barang::all(); 

        return view('transaksi.edit', compact('transaksi', 'barangs'));
    }

    /**
     * Memproses Pembaruan Data Transaksi (Dengan Validasi Batas Stok Aman).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::with('details.barang')->findOrFail($id);

            // 1. KEMBALIKAN STOK BARANG DARI DETAIL LAMA SEBELUM DATA DIHAPUS (Temporary Restore)
            foreach ($transaksi->details as $detailLama) {
                if ($detailLama->barang && strtolower($detailLama->barang->jenis) === 'barang') {
                    $detailLama->barang->increment('stok', $detailLama->jumlah);
                }
            }

            // 2. === VALIDASI STOK SETELAH RESTORE (Pengecekan Apakah Qty Baru Mencukupi) ===
            foreach ($request->items as $item) {
                $barang = Barang::find($item['barang_id']);

                if ($barang && strtolower($barang->jenis) === 'barang') {
                    if ($barang->stok < $item['jumlah']) {
                        DB::rollBack(); // Gagalkan pemulihan stok agar kembali ke kondisi semula
                        return redirect()->back()
                            ->with('error', "Gagal Update! Setelah penyesuaian, stok '{$barang->nama_barang}' tidak mencukupi untuk perubahan baru ini. (Maksimal tersedia setelah dikembalikan: {$barang->stok})");
                    }
                }
            }

            // 3. HAPUS RINCIAN DETAIL TRANSAKSI LAMA
            TransaksiDetail::where('transaksi_id', $transaksi->id)->delete();

            // 4. ISI RINCIAN DATA BARU & POTONG STOK KEMBALI
            $totalBayarBaru = 0;
            foreach ($request->items as $item) {
                $barang = Barang::find($item['barang_id']);
                $subtotal = $barang->harga_jual * $item['jumlah'];
                $totalBayarBaru += $subtotal;

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id'    => $item['barang_id'],
                    'jumlah'       => $item['jumlah'],
                    'subtotal'     => $subtotal,
                ]);

                if ($barang && strtolower($barang->jenis) === 'barang') {
                    $barang->decrement('stok', $item['jumlah']);
                }
            }

            // 5. UPDATE DATA INDUK TRANSAKSI
            $transaksi->update([
                'nama_pelanggan' => $request->nama_pelanggan,
                'total_bayar' => $totalBayarBaru,
            ]);

            // === TRIGGER AUDIT TRAIL: CATAT LOG PERUBAHAN DATA TRANSAKSI ===
            AuditTrail::create([
                'user_id' => auth()->id(),
                'aksi' => 'UPDATE',
                'fitur' => 'Transaksi',
                'deskripsi' => 'Mengubah data transaksi ID #' . $transaksi->id . ' milik pelanggan ' . $transaksi->nama_pelanggan . ' dengan total nominal baru Rp ' . number_format($totalBayarBaru, 0, ',', '.'),
                'ip_address' => $request->ip()
            ]);

            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui dan stok suku cadang telah disesuaikan kembali!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat edit: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus Transaksi & Mengembalikan Stok Utuh Suku Cadang.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::with('details.barang')->findOrFail($id);
            
            // Simpan info penting transaksi sebelum dihapus secara fisik
            $idTransaksi = $transaksi->id;
            $namaPelanggan = $transaksi->nama_pelanggan;

            // KEMBALIKAN STOK SUKU CADANG YANG PERNAH TERPOTONG
            foreach ($transaksi->details as $detail) {
                if ($detail->barang && strtolower($detail->barang->jenis) === 'barang') {
                    $detail->barang->increment('stok', $detail->jumlah);
                }
            }

            // Hapus rincian detail baru kemudian induk transaksinya
            TransaksiDetail::where('transaksi_id', $transaksi->id)->delete();
            $transaksi->delete();

            // === TRIGGER AUDIT TRAIL: CATAT LOG PENGHAPUSAN TRANSAKSI ===
            AuditTrail::create([
                'user_id' => auth()->id(),
                'aksi' => 'DELETE',
                'fitur' => 'Transaksi',
                'deskripsi' => 'Menghapus transaksi ID #' . $idTransaksi . ' milik pelanggan ' . $namaPelanggan . '. Stok suku cadang dikembalikan otomatis ke database.',
                'ip_address' => request()->ip()
            ]);

            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Data riwayat berhasil dihapus, stok suku cadang otomatis kembali utuh!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}