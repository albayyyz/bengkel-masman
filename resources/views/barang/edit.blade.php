<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Barang / Jasa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Ubah Informasi Item</h3>
                
                <form method="post" action="{{ route('barang.update', $barang->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Nama Barang / Jasa</label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang', $barang->nama_barang) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Jenis</label>
                        <select name="jenis" id="edit_jenis_select" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900" onchange="toggleEditStokField(this.value)">
                            <option value="Barang" {{ $barang->jenis == 'Barang' ? 'selected' : '' }}>Barang (Fisik)</option>
                            <option value="Jasa" {{ $barang->jenis == 'Jasa' ? 'selected' : '' }}>Jasa (Mekanik)</option>
                        </select>
                    </div>

                    <div id="edit_stok_container" style="display: {{ $barang->jenis == 'Jasa' ? 'none' : 'block' }};">
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Stok</label>
                        <input type="number" name="stok" id="edit_stok_input" value="{{ old('stok', $barang->stok) }}" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Harga Jual / Tarif Jasa (Rp)</label>
                        <input type="number" name="harga_jual" value="{{ old('harga_jual', $barang->harga_jual) }}" min="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-widest rounded-md shadow-md" style="color: #ffffff !important; background-color: #2563eb !important;">
                            Perbarui Data
                        </button>
                        
                        <button type="button" onclick="window.location.href='{{ route('barang.index') }}'" class="inline-flex items-center px-4 py-2 rounded-md shadow-md text-xs font-bold uppercase tracking-widest" style="color: #ffffff !important; background-color: #4b5563 !important;">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleEditStokField(val) {
            const container = document.getElementById('edit_stok_container');
            const input = document.getElementById('edit_stok_input');
            if(val === 'Jasa') {
                container.style.display = 'none';
                input.value = 0;
            } else {
                container.style.display = 'block';
            }
        }
    </script>
</x-app-layout>