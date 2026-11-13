<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Barang;
use App\Models\Pelanggan;
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
        $pelanggans = Pelanggan::all();

        return view('transaksi.create', compact('barangs', 'pelanggans'));
    }

    /**
     * Menampilkan daftar riwayat transaksi dan analisis omzet.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $queryTransaksi = Transaksi::with(['details.barang', 'pelanggan']);
        $queryDetail = TransaksiDetail::query();

        if ($startDate && $endDate) {
            $queryTransaksi->whereBetween('tanggal_transaksi', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);

            $queryDetail->whereHas('transaksi', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_transaksi', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
            });
        }

        $transaksis = $queryTransaksi->latest('tanggal_transaksi')->get();
        $barangs = Barang::all();

        // Total pendapatan barang.
        $totalSparepart = (clone $queryDetail)
            ->whereHas('barang', function ($query) {
                $query->where(DB::raw('LOWER(jenis)'), 'barang');
            })
            ->sum('subtotal');

        // Total pendapatan jasa.
        $totalJasa = (clone $queryDetail)
            ->whereHas('barang', function ($query) {
                $query->where(DB::raw('LOWER(jenis)'), 'jasa');
            })
            ->sum('subtotal');

        // Total omzet keseluruhan.
        $grandTotalOmset = $startDate && $endDate
            ? Transaksi::whereBetween('tanggal_transaksi', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ])->sum('total_bayar')
            : Transaksi::sum('total_bayar');

        // Data grafik.
        $labels = [];
        $dataOmset = [];

        if ($startDate && $endDate) {
            $period = new \DatePeriod(
                new \DateTime($startDate),
                new \DateInterval('P1D'),
                (new \DateTime($endDate))->modify('+1 day')
            );

            $analitikData = [];

            foreach ($period as $date) {
                $tanggal = $date->format('Y-m-d');
                $analitikData[$tanggal] = 0;
                $labels[] = $date->format('d M');
            }

            $rawChartData = Transaksi::select(
                DB::raw('DATE(tanggal_transaksi) as tanggal'),
                DB::raw('SUM(total_bayar) as total')
            )
                ->whereBetween('tanggal_transaksi', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ])
                ->groupBy(DB::raw('DATE(tanggal_transaksi)'))
                ->get();

            foreach ($rawChartData as $row) {
                if (isset($analitikData[$row->tanggal])) {
                    $analitikData[$row->tanggal] = (int) $row->total;
                }
            }

            $dataOmset = array_values($analitikData);
        } else {
            $labels = [
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ];

            $analitikBulanan = array_fill(1, 12, 0);

            $rawChartData = Transaksi::select(
                DB::raw('MONTH(tanggal_transaksi) as bulan'),
                DB::raw('SUM(total_bayar) as total')
            )
                ->whereYear('tanggal_transaksi', date('Y'))
                ->groupBy(DB::raw('MONTH(tanggal_transaksi)'))
                ->get();

            foreach ($rawChartData as $row) {
                $analitikBulanan[$row->bulan] = (int) $row->total;
            }

            $dataOmset = array_values($analitikBulanan);
        }

        // Mengambil 10 log Audit Trail terbaru.
        $auditTrails = AuditTrail::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('transaksi.index', compact(
            'transaksis',
            'barangs',
            'totalSparepart',
            'totalJasa',
            'grandTotalOmset',
            'startDate',
            'endDate',
            'auditTrails'
        ))->with([
            'chartLabels' => $labels,
            'chartData' => $dataOmset
        ]);
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $items = collect($validated['items'])->map(function ($item) {
                return [
                    'barang_id' => (int) $item['barang_id'],
                    'jumlah' => (int) $item['jumlah'],
                ];
            })->all();

            // Gabungkan jumlah barang yang sama.
            $jumlahBarang = [];

            foreach ($items as $item) {
                $id = $item['barang_id'];
                $jumlahBarang[$id] = ($jumlahBarang[$id] ?? 0) + $item['jumlah'];
            }

            // Kunci data barang agar stok tidak berubah bersamaan.
            $barangs = Barang::whereIn('id', array_keys($jumlahBarang))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($barangs->count() !== count($jumlahBarang)) {
                throw new \RuntimeException('Data barang tidak ditemukan.');
            }

            // Validasi stok.
            foreach ($jumlahBarang as $barangId => $jumlah) {
                $barang = $barangs->get($barangId);

                if (strtolower($barang->jenis) === 'barang' && $barang->stok < $jumlah) {
                    DB::rollBack();

                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            "Stok '{$barang->nama_barang}' tidak mencukupi. Stok tersedia: {$barang->stok}, jumlah diminta: {$jumlah}."
                        );
                }
            }

            // Hitung total berdasarkan harga dari database.
            $totalBayar = 0;

            foreach ($items as $item) {
                $barang = $barangs->get($item['barang_id']);
                $totalBayar += $barang->harga_jual * $item['jumlah'];
            }

            $transaksi = Transaksi::create([
                'pelanggan_id' => $validated['pelanggan_id'],
                'total_bayar' => $totalBayar,
            ]);

            // Simpan detail transaksi.
            foreach ($items as $item) {
                $barang = $barangs->get($item['barang_id']);
                $subtotal = $barang->harga_jual * $item['jumlah'];

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Potong stok berdasarkan total barang.
            foreach ($jumlahBarang as $barangId => $jumlah) {
                $barang = $barangs->get($barangId);

                if (strtolower($barang->jenis) === 'barang') {
                    $barang->decrement('stok', $jumlah);
                }
            }

            $transaksi->load('pelanggan');

            AuditTrail::create([
                'user_id' => auth()->id(),
                'aksi' => 'CREATE',
                'fitur' => 'Transaksi',
                'deskripsi' => 'Membuat transaksi baru untuk pelanggan '
                    . $transaksi->pelanggan->nama_pelanggan
                    . ' dengan total bayar Rp '
                    . number_format($transaksi->total_bayar, 0, ',', '.'),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Transaksi atas nama '
                    . $transaksi->pelanggan->nama_pelanggan
                    . ' berhasil disimpan!')
                ->with('transaksi_id', $transaksi->id);

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Gagal menyimpan transaksi.', [
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan transaksi. Silakan coba kembali.');
        }
    }

    /**
     * Menampilkan form edit transaksi.
     */
    public function edit($id)
    {
        $transaksi = Transaksi::with('details.barang')->findOrFail($id);
        $barangs = Barang::all();
        $pelanggans = Pelanggan::all();

        return view('transaksi.edit', compact(
            'transaksi',
            'barangs',
            'pelanggans'
        ));
    }

    /**
     * Memperbarui transaksi dan menyesuaikan stok.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Kunci transaksi yang sedang diperbarui.
            $transaksi = Transaksi::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $transaksi->load('details');

            $items = collect($validated['items'])->map(function ($item) {
                return [
                    'barang_id' => (int) $item['barang_id'],
                    'jumlah' => (int) $item['jumlah'],
                ];
            })->all();

            // Gabungkan barang lama dan barang baru untuk dikunci.
            $barangIds = collect($items)
                ->pluck('barang_id')
                ->merge($transaksi->details->pluck('barang_id'))
                ->unique()
                ->sort()
                ->values()
                ->all();

            $barangs = Barang::whereIn('id', $barangIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($barangs->count() !== count($barangIds)) {
                throw new \RuntimeException('Data barang transaksi tidak ditemukan.');
            }

            // Kembalikan stok dari detail transaksi lama.
            $stokDikembalikan = [];

            foreach ($transaksi->details as $detailLama) {
                $barang = $barangs->get($detailLama->barang_id);

                if (strtolower($barang->jenis) === 'barang') {
                    $idBarang = $barang->id;

                    $stokDikembalikan[$idBarang] =
                        ($stokDikembalikan[$idBarang] ?? 0) + $detailLama->jumlah;
                }
            }

            foreach ($stokDikembalikan as $barangId => $jumlah) {
                $barangs->get($barangId)->increment('stok', $jumlah);
            }

            // Gabungkan jumlah barang baru.
            $jumlahBarangBaru = [];

            foreach ($items as $item) {
                $idBarang = $item['barang_id'];

                $jumlahBarangBaru[$idBarang] =
                    ($jumlahBarangBaru[$idBarang] ?? 0) + $item['jumlah'];
            }

            // Validasi stok setelah stok lama dikembalikan.
            foreach ($jumlahBarangBaru as $barangId => $jumlah) {
                $barang = $barangs->get($barangId);

                if (strtolower($barang->jenis) === 'barang' && $barang->stok < $jumlah) {
                    DB::rollBack();

                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            "Stok '{$barang->nama_barang}' tidak mencukupi. Stok tersedia: {$barang->stok}, jumlah diminta: {$jumlah}."
                        );
                }
            }

            // Hapus detail lama setelah validasi berhasil.
            TransaksiDetail::where('transaksi_id', $transaksi->id)->delete();

            $totalBayarBaru = 0;

            // Buat detail transaksi baru.
            foreach ($items as $item) {
                $barang = $barangs->get($item['barang_id']);
                $subtotal = $barang->harga_jual * $item['jumlah'];

                $totalBayarBaru += $subtotal;

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $barang->id,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Potong stok berdasarkan jumlah barang baru.
            foreach ($jumlahBarangBaru as $barangId => $jumlah) {
                $barang = $barangs->get($barangId);

                if (strtolower($barang->jenis) === 'barang') {
                    $barang->decrement('stok', $jumlah);
                }
            }

            $transaksi->update([
                'pelanggan_id' => $validated['pelanggan_id'],
                'total_bayar' => $totalBayarBaru,
            ]);

            $transaksi->load('pelanggan');

            AuditTrail::create([
                'user_id' => auth()->id(),
                'aksi' => 'UPDATE',
                'fitur' => 'Transaksi',
                'deskripsi' => 'Mengubah transaksi ID #'
                    . $transaksi->id
                    . ' milik pelanggan '
                    . $transaksi->pelanggan->nama_pelanggan
                    . ' dengan total nominal baru Rp '
                    . number_format($totalBayarBaru, 0, ',', '.'),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('transaksi.index')
                ->with('success', 'Transaksi berhasil diperbarui dan stok telah disesuaikan.');

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Gagal memperbarui transaksi.', [
                'transaksi_id' => $id,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui transaksi. Silakan coba kembali.');
        }
    }

    /**
     * Menghapus transaksi dan mengembalikan stok.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Kunci transaksi yang akan dihapus.
            $transaksi = Transaksi::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $transaksi->load('details');

            $idTransaksi = $transaksi->id;
            $namaPelanggan = Pelanggan::findOrFail($transaksi->pelanggan_id)
                ->nama_pelanggan;

            // Kunci seluruh barang terkait.
            $barangIds = $transaksi->details
                ->pluck('barang_id')
                ->unique()
                ->sort()
                ->values()
                ->all();

            $barangs = Barang::whereIn('id', $barangIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($barangs->count() !== count($barangIds)) {
                throw new \RuntimeException('Data barang transaksi tidak ditemukan.');
            }

            // Kelompokkan stok yang harus dikembalikan.
            $stokDikembalikan = [];

            foreach ($transaksi->details as $detail) {
                $barang = $barangs->get($detail->barang_id);

                if (strtolower($barang->jenis) === 'barang') {
                    $idBarang = $barang->id;

                    $stokDikembalikan[$idBarang] =
                        ($stokDikembalikan[$idBarang] ?? 0) + $detail->jumlah;
                }
            }

            foreach ($stokDikembalikan as $barangId => $jumlah) {
                $barangs->get($barangId)->increment('stok', $jumlah);
            }

            // Hapus detail kemudian transaksi.
            TransaksiDetail::where('transaksi_id', $transaksi->id)->delete();
            $transaksi->delete();

            AuditTrail::create([
                'user_id' => auth()->id(),
                'aksi' => 'DELETE',
                'fitur' => 'Transaksi',
                'deskripsi' => 'Menghapus transaksi ID #'
                    . $idTransaksi
                    . ' milik pelanggan '
                    . $namaPelanggan
                    . '. Stok barang dikembalikan otomatis ke database.',
                'ip_address' => request()->ip(),
            ]);

            DB::commit();

            return redirect()->route('transaksi.index')
                ->with('success', 'Transaksi berhasil dihapus dan stok barang telah dikembalikan.');

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Gagal menghapus transaksi.', [
                'transaksi_id' => $id,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus transaksi. Silakan coba kembali.');
        }
    }
}
