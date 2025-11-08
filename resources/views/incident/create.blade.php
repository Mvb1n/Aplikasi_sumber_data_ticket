<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lapor Insiden Baru') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-8 rounded-lg shadow-md">
                <form action="{{ route('incidents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                            <strong>Whoops! Ada yang salah dengan input Anda:</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="title" :value="__('Judul Insiden')" />
                            <x-text-input type="text" name="title" id="title" required class="mt-1 block w-full" :value="old('title', $incident->title ?? '')" />
                        </div>

                        <div>
                            <x-input-label for="reporter_email" :value="__('Email Pelapor')" />
                            <x-text-input type="email" name="reporter_email" id="reporter_email" required class="mt-1 block w-full" :value="old('reporter_email', $incident->reporter_email ?? '')" />
                        </div>

                        <div>
                            <x-input-label for="site_location_code" :value="__('Pilih Site Kejadian')" />
                            <select name="site_location_code" id="site_location_code" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Pilih Site --</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site['location_code'] }}" data-site-id="{{ $site['id'] }}" @selected(old('site_location_code', $incident->site_location_code ?? '') == $site['location_code'])>{{ $site['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="specific_location" :value="__('Lokasi Spesifik')" />
                            <x-text-input type="text" name="specific_location" id="specific_location" required class="mt-1 block w-full" :value="old('specific_location', $incident->specific_location ?? '')" />
                        </div>

                        <div>
                            <x-input-label for="chronology" :value="__('Kronologi')" />
                            <textarea name="chronology" id="chronology" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('chronology', $incident->chronology ?? '') }}</textarea>
                        </div>

                        {{-- BLOK BARU UNTUK "FILE UMUM" --}}
                        <div>
                            <x-input-label for="incident_files" :value="__('File Pendukung Umum (Tidak terkait aset)')" />
                            <input id="incident_files" name="incident_files[]" type="file" multiple
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <x-input-error :messages="$errors->get('incident_files.*')" class="mt-2" />
                        </div>

                        <!-- Div untuk daftar aset dinamis -->
                        <div>
                            <x-input-label :value="__('Pilih Aset Terlibat (jika ada)')" />
                            <div id="asset-list" class="mt-2 max-h-60 overflow-y-auto border p-4 rounded-md bg-gray-50">
                                <p class="text-gray-500 text-sm">Pilih site terlebih dahulu untuk menampilkan daftar aset.</p>
                            </div>
                            {{-- Input tersembunyi untuk menyimpan nilai awal saat edit --}}
                            <input type="hidden" id="involved_asset_sn_hidden" value="{{ old('involved_asset_sn', $incident->involved_asset_sn ?? '') }}">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('incidents.index') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center mr-4">Batal</a>
                        <x-primary-button>
                            {{ isset($incident) ? 'Simpan Perubahan' : 'Kirim Laporan' }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const siteSelect = document.getElementById('site_location_code');
            const assetListDiv = document.getElementById('asset-list');
            const hiddenInput = document.getElementById('involved_asset_sn_hidden');

            // Ambil nomor seri yang sudah ada dari input tersembunyi
            const currentAssetSNs = hiddenInput.value ? hiddenInput.value.split(',').map(sn => sn.trim()) : [];

            // GANTI FUNGSI fetchAssets LAMA ANDA DENGAN INI

// GANTI FUNGSI LAMA DENGAN FUNGSI BARU INI

function fetchAssets(locationCode) {
    assetListDiv.innerHTML = '<p class="text-sm text-gray-500">Memuat aset...</p>';

    const selectedOption = Array.from(siteSelect.options).find(opt => opt.value === locationCode);
    if (!selectedOption) {
        assetListDiv.innerHTML = '<p class="text-sm text-gray-500">Pilih site yang valid.</p>';
        return;
    }
    const siteId = selectedOption.dataset.siteId;

    const apiUrl = `http://ticket.test:80/api/v1/sites/${siteId}/assets`;
    const apiToken = '1|IQr8L4j8OcmlKhtjGu5bQePz6sOGV8dnuR2Vm0og84164778';

    fetch(apiUrl, {
        headers: { 'Authorization': `Bearer ${apiToken}`, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        assetListDiv.innerHTML = '';
        if (data.length > 0) {
            data.forEach(asset => {
                
                // --- MODIFIKASI DIMULAI DI SINI ---
                
                // Cek apakah aset ini harus dicentang saat load (karena old data)
                const isInitiallyChecked = currentAssetSNs.includes(asset.serial_number);
                const isChecked = isInitiallyChecked ? 'checked' : '';

                // Buat div wrapper dengan Alpine.js
                const assetWrapper = document.createElement('div');
                // PERBAIKAN: Set state 'showUpload' berdasarkan isInitiallyChecked
                assetWrapper.setAttribute('x-data', `{ showUpload: ${isInitiallyChecked ? 'true' : 'false'} }`); 
                assetWrapper.className = 'py-2 border-b border-gray-200 last:border-b-0';

                // Buat label dan checkbox
                const label = document.createElement('label');
                label.className = 'flex items-center space-x-3 cursor-pointer';
                label.innerHTML = `
                    <input type="checkbox" 
                           name="involved_asset_sn[]" 
                           value="${asset.serial_number}" 
                           ${isChecked} 
                           @change="showUpload = !showUpload"
                           class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">${asset.name} (SN: ${asset.serial_number})</span>
                `;
                
                // Buat div untuk form upload file (tersembunyi)
                const uploadDiv = document.createElement('div');
                uploadDiv.setAttribute('x-show', 'showUpload');
                uploadDiv.setAttribute('x-cloak', '');
                uploadDiv.className = 'mt-2 ml-7 pl-3 border-l-2 border-indigo-200';
                
                uploadDiv.innerHTML = `
                    <label class="text-xs font-medium text-gray-600">Upload file untuk aset ini:</label>
                    <input type="file" 
                           name="asset_files[${asset.serial_number}][]" 
                           multiple 
                           class="block w-full text-xs text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-2 file:py-1 file:px-2 file:rounded-l-lg file:border-0 file:text-xs file:font-semibold">
                `;

                // Gabungkan semuanya
                assetWrapper.appendChild(label);
                assetWrapper.appendChild(uploadDiv);
                assetListDiv.appendChild(assetWrapper);

                // --- MODIFIKASI SELESAI ---
            });
        } else {
            assetListDiv.innerHTML = '<p class="text-sm text-gray-500">Tidak ada aset tersedia di site ini.</p>';
        }
    });
}

            if (siteSelect.value) {
                fetchAssets(siteSelect.value);
            }

            siteSelect.addEventListener('change', function() {
                // Saat site diubah, reset daftar aset yang tercentang
                currentAssetSNs.length = 0;
                fetchAssets(this.value);
            });
        });
    </script>
    @endpush
    
</x-app-layout>