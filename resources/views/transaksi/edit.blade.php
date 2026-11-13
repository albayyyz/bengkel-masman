<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg border border-gray-100">
                <div class="flex justify-between items-center mb-5 border-b pb-3">
                    <h2 class="text-xl font-bold text-gray-800">🛠️ Edit Transaksi Nota</h2>
                    <a href="{{ route('transaksi.index') }}" class="text-xs bg-gray-500 text-white font-bold px-3 py-2 rounded">⬅️ Kembali</a>
                </div>

                <form action="{{ route('transaksi.update', $transaksi->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    
                        <div>
                            <label class="block font-bold text-sm text-gray-700 mb-1">Pelanggan:</label>
                            <select name="pelanggan_id" class="w-full border border-gray-300 rounded p-2.5 text-sm" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($pelanggans as $p)
<option value="{{ $p->id }}" {{ old('pelanggan_id', $transaksi->pelanggan_id) == $p->id ? 'selected' : '' }}>
    {{ $p->nama_pelanggan }} - {{ $p->no_telepon }}
</option>
                                @endforeach
                            </select>
                            @error('pelanggan_id')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    <div>
                        <label class="block font-bold text-sm text-gray-700 mb-2">Item Suku Cadang & Jasa Servis:</label>
                        
                        <div id="wrapper-item" class="space-y-3">
                            @foreach($transaksi->details as $index => $detail)
                            <div class="flex flex-col sm:flex-row gap-2 border-b sm:border-none pb-3 sm:pb-0 item-row">
                                <div class="flex-1">
                                    <select name="items[{{ $index }}][barang_id]" class="w-full border border-gray-300 rounded p-2 text-sm item-select" onchange="hitungTotalBaris()" required>
                                        <option value="">-- Pilih Suku Cadang / Jasa --</option>
                                       @foreach($barangs as $b)
    <option value="{{ $b->id }}" data-harga="{{ $b->harga_jual }}" {{ $b->id == $detail->barang_id ? 'selected' : '' }}>
        [{{ strtoupper($b->jenis) }}] {{ $b->nama_barang }} - Rp {{ number_format($b->harga_jual, 0, ',', '.') }}
    </option>
@endforeach
                                    </select>
                                </div>
                                <div class="w-full sm:w-24">
                                    <input type="number" name="items[{{ $index }}][jumlah]" value="{{ $detail->jumlah }}" class="w-full border border-gray-300 rounded p-2 text-sm text-center item-qty" min="1" oninput="hitungTotalBaris()" required>
                                </div>
                                <div class="w-full sm:w-36 relative">
                                    <input type="text" class="w-full border border-gray-300 rounded p-2 text-sm bg-gray-50 font-semibold text-gray-700 item-subtotal-display" readonly>
                                    <input type="hidden" name="items[{{ $index }}][subtotal]" class="item-subtotal-hidden">
                                </div>
                                <button type="button" onclick="hapusBarisItem(this)" class="bg-red-500 hover:bg-red-600 text-white font-bold px-3.5 py-2 rounded text-sm transition shadow-sm">
                                    ✕
                                </button>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" id="btn-tambah-baris" class="mt-3 inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm transition">
                            ➕ Tambah Baris Baru
                        </button>
                    </div>
                    
                    <div class="border-t pt-4">
                        <label class="block font-bold text-gray-800 mb-1 text-base">Total Keseluruhan Nota (Rp):</label>
                        <input type="text" id="total_grand_display" class="w-full border border-gray-400 rounded-lg p-3 bg-gray-900 text-green-400 font-mono font-bold text-2xl shadow-inner tracking-wider" readonly>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full block bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-lg text-center shadow-md text-base uppercase tracking-wider transition">
                            Simpan Perubahan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let indexBaris = {{ $transaksi->details->count() }};

        document.addEventListener('DOMContentLoaded', function () {
            hitungTotalBaris(); // Hitung subtotal langsung saat halaman edit terbuka

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
                if(!opsiTerpilih) return;
                
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
                alert('Peringatan: Minimal transaksi harus memiliki 1 baris item!');
            }
        }
    </script>
      @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-100 border border-red-400 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- isi halaman Edit Transaksi -->
</x-app-layout>