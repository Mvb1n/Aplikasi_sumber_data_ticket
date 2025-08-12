<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Tentukan judul berdasarkan apakah kita sedang mengedit atau membuat baru --}}
            {{ isset($asset) ? __('Edit Aset') : __('Input Aset Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-lg shadow-md">
                
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

                {{-- PERBAIKAN: Buat action dinamis dan method POST --}}
                <form action="{{ isset($asset) ? route('assets.update', $asset) : route('assets.store') }}" method="POST">
                    @csrf
                    
                    {{-- PERBAIKAN: Tambahkan @method('PUT') hanya saat mengedit --}}
                    @if(isset($asset))
                        @method('PUT')
                    @endif

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="name" :value="__('Nama Aset')" />
                            <x-text-input type="text" name="name" id="name" required class="mt-1 block w-full" :value="old('name', $asset->name ?? '')" />
                        </div>
                        <div>
                            <x-input-label for="serial_number" :value="__('Nomor Seri (Tidak bisa diubah)')" />
                            <x-text-input type="text" name="serial_number" id="serial_number" required 
                                          class="mt-1 block w-full bg-gray-100 border-gray-300 cursor-not-allowed" 
                                          :value="old('serial_number', $asset->serial_number ?? '')" 
                                          readonly />
                            {{-- Catatan: 'disabled' akan mencegah data dikirim. 'readonly' lebih aman jika Anda masih butuh datanya di backend. --}}
                        </div>
                        <div>
                            <x-input-label for="category" :value="__('Kategori')" />
                            <x-text-input type="text" name="category" id="category" required class="mt-1 block w-full" :value="old('category', $asset->category ?? 'Perangkat IT')" />
                        </div>
                        <div>
                            <x-input-label for="site_location_code" :value="__('Pilih Site Penempatan')" />
                            <select id="site_location_code" name="site_location_code" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Site --</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site['location_code'] }}" @selected(old('site_location_code', $asset->site_location_code ?? '') == $site['location_code'])>{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="In Use" @selected(old('status', $asset->status ?? '') == 'In Use')>In Use</option>
                                <option value="In Repair" @selected(old('status', $asset->status ?? '') == 'In Repair')>In Repair</option>
                                <option value="Stolen/Lost" @selected(old('status', $asset->status ?? '') == 'Stolen/Lost')>Stolen/Lost</option>
                                <option value="Decommissioned" @selected(old('status', $asset->status ?? '') == 'Decommissioned')>Decommissioned</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('assets.index') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center mr-4">Batal</a>
                        <x-primary-button>
                            {{ isset($asset) ? 'Simpan Perubahan' : 'Tambah Aset' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>