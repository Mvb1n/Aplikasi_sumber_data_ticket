<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Aset Baru (Sumber Data)') }}
        </h2>
    </x-slot>

    <!-- Konten Halaman -->
    <div class="container mx-auto max-w-2xl py-12">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Input Aset Baru</h1>
            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('assets.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Aset</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-700">Nomor Seri</label>
                        <input type="text" name="serial_number" id="serial_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <input type="text" name="category" id="category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="">
                    </div>
                    {{-- Dropdown untuk memilih site, bukan lagi input teks manual --}}
                    <div>
                        <x-input-label for="site_location_code" :value="__('Pilih Site Penempatan')" />
                        <select id="site_location_code" name="site_location_code" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">-- Pilih Site --</option>
                            @forelse($sites as $site)
                                <option value="{{ $site['location_code'] }}" @selected(old('site_location_code') == $site['location_code'])>{{ $site['name'] }}</option>
                            @empty
                                <option value="" disabled>Gagal memuat daftar site. Periksa koneksi API.</option>
                            @endforelse
                        </select>
                    </div>
                     <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <input type="text" name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="In Use">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Simpan & Kirim ke Aplikasi Tiket
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</x-app-layout>