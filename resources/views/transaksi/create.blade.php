<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 bg-white p-6 shadow sm:rounded-lg print:hidden h-fit border border-gray-100">
                    <h2 class="text-base font-bold text-gray-800 border-b pb-2 mb-3 flex items-center gap-2">
                        📋 Petunjuk Kasir Bengkel
                    </h2>
                    <ul class="text-xs text-gray-600 space-y-2 list-disc pl-4 leading-relaxed">
                        <li>Masukkan nama pelanggan/pemilik kendaraan.</li>
                        <li>Gunakan tombol <span class="text-blue-600 font-bold">"➕ Tambah Baris Baru"</span> jika pelanggan membeli lebih dari satu jenis Suku Cadang atau Jasa Servis.</li>
                        <li>Sistem akan mendeteksi tipe item secara otomatis: stok item bertipe <span class="font-bold">Barang</span> akan berkurang, sedangkan item bertipe <span class="font-bold">Jasa</span> tidak akan mengurangi stok ganti.</li>
                    </ul>
                </div>

                <div class="lg:col-span-2 bg-white p-6 shadow sm:rounded-lg border border-gray-100">
                    <h2 class="text-xl font-bold mb-5 text-gray-800 border-b pb-3 print:hidden flex items-center gap-2">
                        🛠️ Form Input Transaksi Terintegrasi
                    </h2>

                    {{-- NOTIFIKASI SUKSES & TRIGER PRINT OTOMATIS --}}
                    @if(session('success'))
                        <div id="notifikasi-sukses" class="bg-green-50 border-l-4 border-green-600 p-4 mb-6 shadow-sm rounded-r print:hidden">
                            <h3 class="font-bold text-lg text-green-900 mb-1">✅ Transaksi Berhasil Diproses!</h3>
                            <p class="text-sm text-green-800 mb-4">{{ session('success') }}</p>
                            <div class="flex gap-2">
                                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-xs font-bold shadow transition duration-150">
                                    🖨️ Cetak Ulang Struk
                                </button>
                                <a href="{{ route('transaksi.create') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-xs font-bold shadow transition duration-150">
                                    ➕ Transaksi Baru
                                </a>
                            </div>
                        </div>

                        {{-- NOTA KECIL THERMAL (Hanya muncul di kertas saat di-print) --}}
                        <div class="hidden print:block text-black font-mono text-xs p-2 w-72 mx-auto">
                            <div class="text-center font-bold text-sm mb-1">CV MASMAN SEJAHTERA</div>
                            <div class="text-center text-[10px] mb-3">Nota Suku Cadang & Jasa Servis Bengkel</div>
                            <hr class="border-dashed border-black my-1">
                            <p>Tanggal   : {{ now()->format('d-m-Y H:i') }} WIB</p>
                            <p>Pelanggan : {{ session('nama_pelanggan') ?? 'Umum' }}</p>
                            <hr class="border-dashed border-black my-1">
                            
                            <!-- Memanggil data transaksi terakhir pelanggan ini untuk membaca item aslinya -->
                            @php
                                $transaksiTerakhir = \App\Models\Transaksi::with('details.barang')
                                                                        ->where('nama_pelanggan', session('nama_pelanggan'))
                                                                        ->latest()
                                                                        ->first();
                            @endphp

                            @if($transaksiTerakhir)
                                <!-- KELOMPOK BARANG / SPAREPART -->
                                @php $adaBarang = false; @endphp
                                @foreach($transaksiTerakhir->details as $d)
                                    @if(($d->barang->jenis ?? '') === 'Barang')
                                        @if(!$adaBarang)
                                            <p class="font-bold mt-2">[ SUKU CADANG / SPAREPART ]</p>
                                            @php $adaBarang = true; @endphp
                                        @endif
                                        <div class="flex justify-between pl-1">
                                            <span>- {{ $d->barang->nama_barang }} (x{{ $d->jumlah }})</span>
                                            <span>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                @endforeach

                                <!-- KELOMPOK JASA SERVIS -->
                                @php $adaJasa = false; @endphp
                                @foreach($transaksiTerakhir->details as $d)
                                    @if(($d->barang->jenis ?? '') === 'Jasa')
                                        @if(!$adaJasa)
                                            <p class="font-bold mt-2">[ TINDAKAN JASA SERVIS ]</p>
                                            @php $adaJasa = true; @endphp
                                        @endif
                                        <div class="flex justify-between pl-1">
                                            <span>- {{ $d->barang->nama_barang }} (x{{ $d->jumlah }})</span>
                                            <span>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                @endforeach

                                <hr class="border-dashed border-black my-2">
                                <div class="flex justify-between font-bold text-sm">
                                    <span>TOTAL NOTA:</span>
                                    <span>Rp {{ number_format($transaksiTerakhir->total_bayar, 0, ',', '.') }}</span>
                                </div>
                            @else
                                <p class="text-center italic text-[10px] my-4">Gagal memuat rincian belanja.</p>
                            @endif

                            <hr class="border-dashed border-black my-2">
                            <div class="text-center text-[10px] mt-4 font-bold">TERIMA KASIH ATAS KEPERCAYAAN ANDA</div>
                        </div>
                    @endif

                    {{-- === BERHASIL DITAMBAHKAN: NOTIFIKASI ERROR STOK HABIS === --}}
                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 shadow-sm rounded-r print:hidden">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">❌</span>
                                <h3 class="font-bold text-base text-red-900">Transaksi Gagal Diproses!</h3>
                            </div>
                            <p class="text-sm text-red-800 mt-1 pl-7">{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('transaksi.store') }}" method="POST" class="space-y-5 print:hidden">
                        @csrf
                        
                        <div>
                            <label class="block font-bold text-sm text-gray-700 mb-1">Nama Pelanggan:</label>
                            <input type="text" name="nama_pelanggan" class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Contoh: Budi - Avanza B 1234 ABC" required>
                        </div>

                        <div>
                            <label class="block font-bold text-sm text-gray-700 mb-2">Item Suku Cadang & Jasa Servis:</label>
                            
                            <div id="wrapper-item" class="space-y-3">
                                <div class="flex flex-col sm:flex-row gap-2 border-b sm:border-none pb-3 sm:pb-0 item-row">
                                    <div class="flex-1">
                                        <select name="items[0][barang_id]" class="w-full border border-gray-300 rounded p-2 text-sm item-select" onchange="hitungTotalBaris()" required>
                                            <option value="">-- Pilih Suku Cadang / Jasa --</option>
                                            @foreach($barangs as $b)
                                                <option value="{{ $b->id }}" data-harga="{{ $b->harga_jual }}">
                                                    [{{ strtoupper($b->jenis) }}] {{ $b->nama_barang }} - Rp {{ number_format($b->harga_jual, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-full sm:w-24">
                                        <input type="number" name="items[0][jumlah]" class="w-full border border-gray-300 rounded p-2 text-sm text-center item-qty" placeholder="Qty" min="1" value="1" oninput="hitungTotalBaris()" required>
                                    </div>
                                    <div class="w-full sm:w-36 relative">
                                        <input type="text" class="w-full border border-gray-300 rounded p-2 text-sm bg-gray-50 font-semibold text-gray-700 item-subtotal-display" placeholder="Rp 0" readonly>
                                        <input type="hidden" name="items[0][subtotal]" class="item-subtotal-hidden">
                                    </div>
                                    <button type="button" onclick="hapusBarisItem(this)" class="bg-red-500 hover:bg-red-600 text-white font-bold px-3.5 py-2 rounded text-sm transition shadow-sm">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <button type="button" id="btn-tambah-baris" class="mt-3 inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm transition">
                                ➕ Tambah Baris Baru
                            </button>
                        </div>
                        
                        <div class="border-t pt-4">
                            <label class="block font-bold text-gray-800 mb-1 text-base">Total Keseluruhan Nota (Rp):</label>
                            <input type="text" id="total_grand_display" class="w-full border border-gray-400 rounded-lg p-3 bg-gray-900 text-green-400 font-mono font-bold text-2xl shadow-inner tracking-wider" value="Rp 0" readonly>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full block text-white font-bold py-3.5 px-4 rounded-lg text-center shadow-md text-base uppercase tracking-wider transition duration-150 cursor-pointer"
                                    style="background-color: #16a34a !important; color: #ffffff !important; display: block !important; width: 100% !important;">
                                Simpan Transaksi & Cetak Struk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let indexBaris = 1;

        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('notifikasi-sukses')) {
                window.print();
            }

            document.getElementById('btn-tambah-baris').addEventListener('click', function() {
                const container = document.getElementById('wrapper-item');
                const divBaru = document.createElement('div');
                divBaru.className = "flex flex-col sm:flex-row gap-2 border-b sm:border-none pb-3 sm:pb-0 item-row";
                
                divBaru.innerHTML = `
                    <div class="flex-1">
                        <select name="items[${indexBaris}][barang_id]" class="w-full border border-gray-300 rounded p-2 text-sm item-select" onchange="hitungTotalBaris()" required>
                            <option value="">-- Pilih Suku Cadang / Jasa --</option>
                            @foreach($barangs as $b)
                                <option value="{{ $b->id }}" data-harga="{{ $b->harga_jual }}">
                                    [{{ strtoupper($b->jenis) }}] {{ $b->nama_barang }} - Rp {{ number_format($b->harga_jual, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-24">
                        <input type="number" name="items[${indexBaris}][jumlah]" class="w-full border border-gray-300 rounded p-2 text-sm text-center item-qty" placeholder="Qty" min="1" value="1" oninput="hitungTotalBaris()" required>
                    </div>
                    <div class="w-full sm:w-36 relative">
                        <input type="text" class="w-full border border-gray-300 rounded p-2 text-sm bg-gray-50 font-semibold text-gray-700 item-subtotal-display" placeholder="Rp 0" readonly>
                        <input type="hidden" name="items[${indexBaris}][subtotal]" class="item-subtotal-hidden">
                    </div>
                    <button type="button" onclick="hapusBarisItem(this)" class="bg-red-500 hover:bg-red-600 text-white font-bold px-3.5 py-2 rounded text-sm transition shadow-sm">
                        ✕
                    </button>
                `;
                
                container.appendChild(divBaru);
                indexBaris++;
            });
        });

        function hitungTotalBaris() {
            let totalMurni = 0;
            const semuaBaris = document.querySelectorAll('.item-row');

            semuaBaris.forEach(baris => {
                const selectItem = baris.querySelector('.item-select');
                const qtyInput = baris.querySelector('.item-qty');
                const displaySubtotal = baris.querySelector('.item-subtotal-display');
                const hiddenSubtotal = baris.querySelector('.item-subtotal-hidden');

                const opsiTerpilih = selectItem.options[selectItem.selectedIndex];
                const hargaSatuan = opsiTerpilih.getAttribute('data-harga');
                const kuantitas = qtyInput.value;

                if (hargaSatuan && kuantitas) {
                    const hasilSubtotal = parseInt(hargaSatuan) * parseInt(kuantitas);
                    displaySubtotal.value = "Rp " + new Intl.NumberFormat('id-ID').format(hasilSubtotal);
                    hiddenSubtotal.value = hasilSubtotal;
                    totalMurni += hasilSubtotal;
                } else {
                    displaySubtotal.value = '';
                    hiddenSubtotal.value = '';
                }
            });

            document.getElementById('total_grand_display').value = "Rp " + new Intl.NumberFormat('id-ID').format(totalMurni);
        }

        function hapusBarisItem(elemen) {
            const listBaris = document.querySelectorAll('.item-row');
            
            if (listBaris.length > 1) {
                elemen.parentElement.remove();
                hitungTotalBaris();
            } else {
                alert('Peringatan: Minimal transaksi harus memiliki 1 baris item suku cadang atau jasa!');
            }
        }
    </script>
</x-app-layout>