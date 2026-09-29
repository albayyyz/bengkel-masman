<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Ubah Informasi Pelanggan</h3>
                
                <form method="post" action="{{ route('pelanggan.update', $pelanggan->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">No. Telepon</label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon', $pelanggan->no_telepon) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1">Alamat</label>
                        <textarea name="alamat" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                    </div>

                    <div class="flex space-x-3 pt-4">
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-widest rounded-md shadow-md transition duration-150 ease-in-out" style="color: #ffffff !important; background-color: #2563eb !important;">
        Perbarui Data
    </button>
    
    <button type="button" onclick="window.location.href='{{ route('pelanggan.index') }}'" class="inline-flex items-center px-4 py-2 rounded-md shadow-md text-xs font-bold uppercase tracking-widest transition duration-150 ease-in-out" style="color: #ffffff !important; background-color: #4b5563 !important;">
        Batal
    </button>
</div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>