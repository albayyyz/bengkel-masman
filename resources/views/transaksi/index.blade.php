<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Bagian Atas / Header --}}
            <div class="flex justify-between items-center print:hidden">
                <h2 class="text-xl font-bold text-gray-800">📋 Riwayat Transaksi Bengkel</h2>
                <a href="{{ route('transaksi.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition">
                    ➕ Kasir Baru
                </a>
            </div>

            {{-- === FORM FILTER TANGGAL / KALENDER === --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 print:hidden">
                <form action="{{ route('transaksi.index') }}" method="GET" id="formFilterTanggal" class="flex flex-col gap-4 lg:flex-row lg:items-end justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 flex-1">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="startDate" value="{{ $startDate }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="endDate" value="{{ $endDate }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 shadow-sm">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="setShortcutFilter('hari_ini')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-3 py-2 rounded-lg transition">Hari Ini</button>
                        <button type="button" onclick="setShortcutFilter('bulan_ini')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-3 py-2 rounded-lg transition">Bulan Ini</button>
                        <button type="button" onclick="setShortcutFilter('bulan_lalu')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-3 py-2 rounded-lg transition">Bulan Lalu</button>
                        <button type="button" onclick="setShortcutFilter('3_bulan')" class="bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold px-3 py-2 rounded-lg transition">⏱️ 3 Bulan Terakhir</button>
                        
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-5 py-2 rounded-lg shadow transition lg:ml-2">
                            🔍 Terapkan Filter
                        </button>
                        @if($startDate || $endDate)
                            <a href="{{ route('transaksi.index') }}" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold px-3 py-2 rounded-lg transition" title="Reset Filter">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- === CARD DASHBOARD RINGKASAN LAPORAN KEUANGAN === --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 print:hidden">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between transition hover:shadow-md">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pendapatan Suku Cadang</p>
                        <h3 class="text-xl font-extrabold text-blue-600 mt-1 font-mono">
                            Rp {{ number_format($totalSparepart, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg text-xl">
                        ⚙️
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between transition hover:shadow-md">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pendapatan Jasa Servis</p>
                        <h3 class="text-xl font-extrabold text-amber-600 mt-1 font-mono">
                            Rp {{ number_format($totalJasa, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-lg text-xl">
                        👨‍🔧
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-5 rounded-xl shadow-md flex items-center justify-between transition hover:shadow-lg text-white">
                    <div>
                        <p class="text-xs font-bold text-green-100 uppercase tracking-wider">Total Pendapatan (Omset)</p>
                        <h3 class="text-2xl font-black mt-1 font-mono text-white">
                            Rp {{ number_format($grandTotalOmset, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg text-xl">
                        💰
                    </div>
                </div>
            </div>

            {{-- === GRAFIK ANALITIK TREN OMSET BENGKEL === --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 print:hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">📊 Grafik Analitik Tren Omset</h3>
                        <p class="text-xs text-gray-500">
                            @if($startDate && $endDate)
                                Menampilkan pergerakan omset dari tanggal {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}
                            @else
                                Acuan naik/turun performa keuangan bulanan bengkel tahun ini (Default)
                            @endif
                        </p>
                    </div>
                    <div class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full font-semibold mt-2 sm:mt-0">
                        Status: Disesuaikan
                    </div>
                </div>
                <div class="w-full relative" style="height: 280px;">
                    <canvas id="chartOmsetBengkel"></canvas>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-4 shadow-sm rounded-r print:hidden">
                    <p class="text-sm text-green-800 font-semibold">✅ {{ session('success') }}</p>
                </div>
            @endif

            {{-- Tabel Riwayat Transaksi --}}
            <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-gray-100 print:hidden">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Pelanggan</th>
                                    <th class="px-4 py-3">Rincian Item</th>
                                    <th class="px-4 py-3 text-right">Total Bayar</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </table>
                            <tbody>
                                @forelse($transaksis as $index => $t)
                                    <tr class="bg-white border-b hover:bg-gray-50 transition">
                                        <td class="px-4 py-3.5 text-center font-mono text-gray-600">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-600">
                                            {{ $t->created_at->format('d-m-Y H:i') }} WIB
                                        </td>
                                        <td class="px-4 py-3.5 font-semibold text-gray-800">{{ $t->nama_pelanggan }}</td>
                                        <td class="px-4 py-3.5">
                                            <ul class="text-xs space-y-1 text-gray-600 list-disc pl-3">
                                                @foreach($t->details as $d)
                                                    <li>{{ $d->barang->nama_barang ?? 'Item Dihapus' }} <span class="font-bold text-gray-800">(x{{ $d->jumlah }})</span></li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-bold text-gray-900 font-mono">
                                            Rp {{ number_format($t->total_bayar, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-1.5">
                                                
                                                <button onclick="cetakNotaUlang('nota-{{ $t->id }}')" 
                                                        class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 rounded text-xs font-bold shadow-sm transition duration-150 flex items-center gap-1" title="Cetak Ulang Struk">
                                                    🖨️ Struk
                                                </button>

                                                <a href="{{ route('transaksi.edit', $t->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1.5 rounded text-xs font-bold shadow-sm transition">
                                                    ✏️ Edit
                                                </a>
                                                <form action="{{ route('transaksi.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok suku cadang akan dikembalikan otomatis!')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2.5 py-1.5 rounded text-xs font-bold shadow-sm transition">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- === ELEMEN TERSEMBUNYI: FORMAT STRUK THERMAL UNTUK MATRIKS PRINT === --}}
                                    <div id="nota-{{ $t->id }}" class="hidden-print-template hidden font-mono text-xs p-2 w-72 mx-auto text-black" style="display: none;">
                                        <div style="font-weight: bold; text-align: center; font-size: 14px; margin-bottom: 2px;">CV MASMAN SEJAHTERA</div>
                                        <div style="text-align: center; font-size: 10px; margin-bottom: 10px;">Nota Suku Cadang & Jasa Servis Bengkel</div>
                                        <div style="border-top: 1px dashed black; margin: 4px 0;"></div>
                                        <p style="margin: 2px 0;">Nota ID    : #{{ $t->id }}</p>
                                        <p style="margin: 2px 0;">Tanggal    : {{ $t->created_at->format('d-m-Y H:i') }} WIB</p>
                                        <p style="margin: 2px 0;">Pelanggan  : {{ $t->nama_pelanggan }}</p>
                                        <div style="border-top: 1px dashed black; margin: 4px 0;"></div>
                                        
                                        @php $adaBarang = false; @endphp
                                        @foreach($t->details as $d)
                                            @if(($d->barang->jenis ?? '') === 'Barang')
                                                @if(!$adaBarang)
                                                    <p style="font-weight: bold; margin-top: 6px; margin-bottom: 4px;">[ SUKU CADANG / SPAREPART ]</p>
                                                    @php $adaBarang = true; @endphp
                                                @endif
                                                <div class="flex-row">
                                                    <span class="item-name">- {{ $d->barang->nama_barang ?? 'Item Dihapus' }} (x{{ $d->jumlah }})</span>
                                                    <span class="text-right">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                        @endforeach

                                        @php $adaJasa = false; @endphp
                                        @foreach($t->details as $d)
                                            @if(($d->barang->jenis ?? '') === 'Jasa')
                                                @if(!$adaJasa)
                                                    <p style="font-weight: bold; margin-top: 8px; margin-bottom: 4px;">[ TINDAKAN JASA SERVIS ]</p>
                                                    @php $adaJasa = true; @endphp
                                                @endif
                                                <div class="flex-row">
                                                    <span class="item-name">- {{ $d->barang->nama_barang ?? 'Item Dihapus' }} (x{{ $d->jumlah }})</span>
                                                    <span class="text-right">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                        @endforeach

                                        <div style="border-top: 1px dashed black; margin: 8px 0;"></div>
                                        <div class="flex-row" style="font-weight: bold; font-size: 13px;">
                                            <span>TOTAL NOTA:</span>
                                            <span class="text-right">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</span>
                                        </div>
                                        <div style="border-top: 1px dashed black; margin: 8px 0;"></div>
                                        <div style="text-align: center; font-size: 10px; margin-top: 12px; font-weight: bold;">TERIMA KASIH ATAS KEPERCAYAAN ANDA</div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Belum ada riwayat transaksi masuk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- === SEKSI AUDIT TRAIL / LOG AKTIVITAS TERBARU === --}}
            <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-gray-100 print:hidden mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🛡️ Log Aktivitas Sistem (Audit Trail - Realtime)
                        </h3>
                        <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1 rounded-full">
                            Keamanan Aktivitas UMKM
                        </span>
                    </div>
                    
                    <p class="text-xs text-gray-500 mb-4">
                        Menampilkan rekaman log manipulasi data transaksi terakhir untuk keperluan validasi transparansi sistem dan audit operasional bengkel <strong>CV Masman Sejahtera</strong>.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-center">Waktu</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                    <th class="px-4 py-3">Deskripsi Log Aktivitas</th>
                                    <th class="px-4 py-3 text-center">Alamat IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($activityLogs ?? [] as $log)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-600">
                                            {{ $log->created_at->format('d-m-Y H:i:s') }} WIB
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800 text-xs">
                                            {{ $log->user->name ?? 'System/Guest' }}
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if($log->aksi === 'CREATE')
                                                <span class="bg-green-100 text-green-800 text-xs font-extrabold px-2.5 py-0.5 rounded shadow-sm">CREATE</span>
                                            @elseif($log->aksi === 'UPDATE')
                                                <span class="bg-amber-100 text-amber-800 text-xs font-extrabold px-2.5 py-0.5 rounded shadow-sm">UPDATE</span>
                                            @elseif($log->aksi === 'DELETE')
                                                <span class="bg-red-100 text-red-800 text-xs font-extrabold px-2.5 py-0.5 rounded shadow-sm">DELETE</span>
                                            @else
                                                <span class="bg-gray-100 text-gray-800 text-xs font-extrabold px-2.5 py-0.5 rounded shadow-sm">{{ $log->aksi }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-700 font-mono max-w-md break-words">
                                            <span class="font-bold text-gray-900">[{{ $log->fitur }}]</span> {{ $log->deskripsi }}
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap text-xs font-mono text-gray-500">
                                            {{ $log->ip_address }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-xs">
                                            Belum ada log aktivitas tercatat. Coba lakukan transaksi baru atau edit transaksi lama!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- === SCRIPT LIBRARY CHART.JS VIA CDN === --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Ambil array data dan labels hasil olahan dinamis dari controller Laravel
            const labelLaravel = @json($chartLabels);
            const dataOmsetLaravel = @json($chartData);

            const ctx = document.getElementById('chartOmsetBengkel').getContext('2d');
            
            // Efek gradien di bawah garis grafik
            const gradientBg = ctx.createLinearGradient(0, 0, 0, 280);
            gradientBg.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
            gradientBg.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            new Chart(ctx, {
                type: 'line', 
                data: {
                    labels: labelLaravel,
                    datasets: [{
                        label: 'Omset',
                        data: dataOmsetLaravel,
                        borderColor: '#10b981', 
                        borderWidth: 3,
                        backgroundColor: gradientBg,
                        fill: true,
                        tension: 0.2, 
                        pointBackgroundColor: '#10b981',
                        pointRadius: labelLaravel.length > 31 ? 2 : 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ' Omset: Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                    return 'Rp ' + value;
                                },
                                font: { size: 10 }
                            },
                            grid: { color: 'rgba(243, 244, 246, 1)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        });

        {{-- === SCRIPT SHORTCUT TOMBOL FILTER TANGGAL (JS CLIENT-SIDE) === --}}
        function setShortcutFilter(tipe) {
            const inputStart = document.getElementById('startDate');
            const inputEnd = document.getElementById('endDate');
            
            const hariIni = new Date();
            let tglMulai = new Date();
            let tglSelesai = new Date();

            const formatTanggal = (date) => {
                let d = date.getDate().toString().padStart(2, '0');
                let m = (date.getMonth() + 1).toString().padStart(2, '0');
                let y = date.getFullYear();
                return `${y}-${m}-${d}`;
            };

            switch(tipe) {
                case 'hari_ini':
                    tglMulai = hariIni;
                    tglSelesai = hariIni;
                    break;
                case 'bulan_ini':
                    tglMulai = new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);
                    tglSelesai = new Date(hariIni.getFullYear(), hariIni.getMonth() + 1, 0);
                    break;
                case 'bulan_lalu':
                    tglMulai = new Date(hariIni.getFullYear(), hariIni.getMonth() - 1, 1);
                    tglSelesai = new Date(hariIni.getFullYear(), hariIni.getMonth(), 0);
                    break;
                case '3_bulan':
                    tglMulai = new Date(hariIni.getFullYear(), hariIni.getMonth() - 3, hariIni.getDate());
                    tglSelesai = hariIni;
                    break;
            }

            inputStart.value = formatTanggal(tglMulai);
            inputEnd.value = formatTanggal(tglSelesai);

            document.getElementById('formFilterTanggal').submit();
        }

        {{-- === JAVASCRIPT ENGINE UNTUK MENCETAK NOTA === --}}
        function cetakNotaUlang(divId) {
            const isiNota = document.getElementById(divId).innerHTML;
            const jendelaPrint = window.open('', '_blank', 'width=400,height=600');
            
            jendelaPrint.document.write(`
                <html>
                <head>
                    <title>Cetak Ulang Nota Bengkel</title>
                    <style>
                        body { 
                            font-family: 'Courier New', Courier, monospace; 
                            font-size: 12px; 
                            color: black;
                            padding: 10px;
                            width: 260px;
                            margin: 0 auto;
                        }
                        div { box-sizing: border-box; }
                        .flex-row {
                            display: flex !important;
                            justify-content: space-between !important;
                            align-items: flex-start !important;
                            width: 100% !important;
                            margin-bottom: 3px;
                        }
                        .item-name {
                            text-align: left !important;
                            word-break: break-word;
                            padding-right: 5px;
                        }
                        .text-right {
                            text-align: right !important;
                            white-space: nowrap !important;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    \${isiNota}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 500);
                        }
                    <\/script>
                </body>
                </html>
            `);
            jendelaPrint.document.close();
        }
    </script>
</x-app-layout>