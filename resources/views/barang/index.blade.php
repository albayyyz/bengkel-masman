<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Barang & Jasa Bengkel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg shadow-sm border border-green-200" role="alert">
                    <span class="font-bold">Berhasil!</span> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow-sm border border-red-200" role="alert">
        <span class="font-bold">Gagal!</span> {{ session('error') }}
    </div>
@endif

            @if ($errors->any())
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow-sm border border-red-200" role="alert">
                    <span class="font-bold">Gagal Menyimpan Data!</span>
                    <ul class="mt-2 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Item / Jasa Baru</h3>
                
                <form method="post" action="{{ route('barang.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-1">Nama Barang / Jasa</label>
                            <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" placeholder="Contoh: Oli MPX 2, Kampas Rem Depan, Aki Motobatt" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-1">Jenis</label>
                            <select name="jenis" id="jenis_select" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900" onchange="toggleStokField(this.value)">
                                <option value="Barang" {{ old('jenis') == 'Barang' ? 'selected' : '' }}>Barang (Fisik / Sparepart)</option>
                                <option value="Jasa" {{ old('jenis') == 'Jasa' ? 'selected' : '' }}>Jasa (Mekanik / Servis)</option>
                            </select>
                        </div>

                        <div id="stok_container">
                            <label class="block text-sm font-semibold text-gray-800 mb-1">Stok Awal</label>
                            <input type="number" name="stok" id="stok_input" value="{{ old('stok', 0) }}" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                        </div>

                        <div>
                            <label id="harga_label" class="block text-sm font-semibold text-gray-800 mb-1">Harga Jual (Rp)</label>
                            <input type="number" name="harga_jual" value="{{ old('harga_jual') }}" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs uppercase tracking-widest rounded-md shadow-md">
                            Simpan Item
                        </button>
                    </div>
                </form>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Stok Barang & Sparepart</h3>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800">Item Fisik</span>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200 border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Sparepart</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Stok Sisa</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-48">Harga Jual</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $noBarang = 1; @endphp
                        @forelse($barang->where('jenis', 'Barang') as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $noBarang++ }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nama_barang }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span class="font-semibold {{ $item->stok < 5 ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                        {{ $item->stok }} Pcs
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm font-medium flex space-x-3">
                                    <a href="{{ route('barang.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('barang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus sparepart ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data barang / sparepart fisik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Ongkos & Jasa Servis</h3>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">Jasa Mekanik</span>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200 border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Jasa / Tindakan</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-48">Tarif Jasa</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $noJasa = 1; @endphp
                        @forelse($barang->where('jenis', 'Jasa') as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $noJasa++ }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nama_barang }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm font-medium flex space-x-3">
                                    <a href="{{ route('barang.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('barang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jasa ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data tindakan/jasa servis.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        function toggleStokField(val) {
            const container = document.getElementById('stok_container');
            const input = document.getElementById('stok_input');
            const namaInput = document.getElementsByName('nama_barang')[0];
            const hargaLabel = document.getElementById('harga_label');
            
            if(val === 'Jasa') {
                container.style.visibility = 'hidden'; 
                input.value = 0;
                namaInput.placeholder = "Contoh: Servis Ringan, Ganti Oli, Pasang Ban, Tune Up";
                hargaLabel.innerText = "Tarif Jasa / Ongkos (Rp)";
            } else {
                container.style.visibility = 'visible';
                namaInput.placeholder = "Contoh: Oli MPX 2, Kampas Rem Depan, Aki Motobatt";
                hargaLabel.innerText = "Harga Jual (Rp)";
            }
        }

        // Jalankan fungsi saat pertama kali halaman di-load untuk menyesuaikan state "old" inputan
        document.addEventListener("DOMContentLoaded", function() {
            const jenisSelect = document.getElementById('jenis_select');
            if(jenisSelect) {
                toggleStokField(jenisSelect.value);
            }
        });
    </script>
</x-app-layout>