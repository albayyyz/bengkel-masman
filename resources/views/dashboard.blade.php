<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Sistem Utama') }}
            </h2>
            <span class="text-sm bg-blue-100 text-blue-800 font-medium px-3 py-1 rounded-full shadow-sm">
                🗓️ {{ now()->format('d F Y') }}
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- SECTION 1: PROFIL BENGKEL & FOTO -->
            <div class="bg-white overflow-hidden shadow sm:rounded-lg border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 items-center">
                    
                    <!-- Kolom Kiri & Tengah: Info Bengkel -->
                    <div class="md:col-span-2 space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 text-green-700 rounded-md text-xs font-bold uppercase tracking-wider">
                            🔧 Bengkel Aktif
                        </div>
                        <h1 class="text-3xl font-extrabold text-gray-950 tracking-tight">
                            CV Masman Sejahtera
                        </h1>
                        <p class="text-gray-600 leading-relaxed text-sm">
                            Selamat datang di sistem manajemen terintegrasi pelayanan kasir, pendataan barang Suku Cadang (*sparepart*), manajemen pelanggan, serta pemantauan stok otomatis secara real-time.
                        </p>
                        <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-500 font-medium">
                            <div class="flex items-center gap-2">
                                📍 <span class="text-gray-700">Lokasi: Workshop Utama CV Masman Sejahtera</span>
                            </div>
                            <div class="flex items-center gap-2">
                                🔑 <span class="text-gray-700">Operator: {{ Auth::user()->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Slot Foto Bengkel -->
                    <div class="md:col-span-1 flex justify-center">
                        <div class="w-full h-44 bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center relative overflow-hidden group hover:border-blue-400 transition-colors p-2 text-center">
                            
                            {{-- TIPS MEMASANG FOTO ASLI: 
                                 1. Taruh foto bengkel Anda di folder public/images/bengkel.jpg
                                 2. Hapus tanda komentar {{-- dan --}} pada baris tag <img> di bawah ini
                            --}}

                            {{-- 
                            <img src="{{ asset('images/bengkel.jpg') }}" alt="Foto CV Masman Sejahtera" class="w-full h-full object-cover rounded-lg absolute inset-0">
                            --}}

                            <!-- Placeholder jika foto belum dimasukkan -->
                            <div class="space-y-1 text-gray-400 pointer-events-none">
                                <span class="text-4xl block">🏢</span>
                                <p class="text-xs font-bold text-gray-600">Slot Foto Bengkel</p>
                                <p class="text-[10px] text-gray-400 px-2">Taruh file foto di <code class="bg-gray-200 text-red-600 px-1 rounded">public/images/bengkel.jpg</code> lalu aktifkan tag img</p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 2: SHORTCUT KARTU NAVIGASI UTAMA -->
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Menu Pintasan Cepat</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- Card 1: Kasir -->
                    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center text-lg font-bold mb-3">🛒</div>
                            <h4 class="font-bold text-gray-900 text-base mb-1">Form Kasir</h4>
                            <p class="text-xs text-gray-500 mb-4 leading-normal">Input transaksi penjualan barang atau jasa ke pelanggan dan cetak struk otomatis.</p>
                        </div>
                        <a href="{{ route('transaksi.create') }}" class="w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition shadow-sm">
                            Buka Kasir &rarr;
                        </a>
                    </div>

                    <!-- Card 2: Riwayat Transaksi -->
                    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold mb-3">📋</div>
                            <h4 class="font-bold text-gray-900 text-base mb-1">Riwayat Transaksi</h4>
                            <p class="text-xs text-gray-500 mb-4 leading-normal">Lihat rekapitulasi data seluruh penjualan, omset kasir, dan detail item terlaris.</p>
                        </div>
                        <a href="{{ route('transaksi.index') }}" class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition shadow-sm">
                            Buka Riwayat &rarr;
                        </a>
                    </div>

                    <!-- Card 3: Barang & Jasa -->
                    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold mb-3">🛠️</div>
                            <h4 class="font-bold text-gray-900 text-base mb-1">Barang & Jasa</h4>
                            <p class="text-xs text-gray-500 mb-4 leading-normal">Kelola master data stok suku cadang bengkel, harga beli, dan tarif jasa servis.</p>
                        </div>
                        <a href="{{ route('barang.index') }}" class="w-full text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-3 rounded-lg text-xs transition shadow-sm">
                            Kelola Stok &rarr;
                        </a>
                    </div>

                    <!-- Card 4: Pelanggan -->
                    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold mb-3">👥</div>
                            <h4 class="font-bold text-gray-900 text-base mb-1">Data Pelanggan</h4>
                            <p class="text-xs text-gray-500 mb-4 leading-normal">Manajemen data kendaraan atau riwayat pelanggan tetap untuk optimasi layanan.</p>
                        </div>
                        <a href="{{ route('pelanggan.index') }}" class="w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded-lg text-xs transition shadow-sm">
                            Lihat Pelanggan &rarr;
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>