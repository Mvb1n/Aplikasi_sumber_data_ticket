<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lapor Insiden Baru') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <form action="{{ route('incidents.store') }}" method="POST">
                    @csrf
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

            function fetchAssets(locationCode) {
                assetListDiv.innerHTML = '<p class="text-sm text-gray-500">Memuat aset...</p>';

                const selectedOption = Array.from(siteSelect.options).find(opt => opt.value === locationCode);
                if (!selectedOption) {
                    assetListDiv.innerHTML = '<p class="text-sm text-gray-500">Pilih site yang valid.</p>';
                    return;
                }
                const siteId = selectedOption.dataset.siteId;

                const apiUrl = `http://ticket.test:80/api/v1/sites/${siteId}/assets`;
                const apiToken = '1|fbipjMTqgLrTeV9xZIs4HoaQju1D0tWtCMOXlzmR3bc34c27';

                fetch(apiUrl, {
                    headers: { 'Authorization': `Bearer ${apiToken}`, 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    assetListDiv.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(asset => {
                            const isChecked = currentAssetSNs.includes(asset.serial_number) ? 'checked' : '';
                            const label = document.createElement('label');
                            label.className = 'flex items-center space-x-3';
                            label.innerHTML = `
                                <input type="checkbox" name="involved_asset_sn[]" value="${asset.serial_number}" ${isChecked} class="rounded border-gray-300">
                                <span class="text-sm text-gray-700">${asset.name} (SN: ${asset.serial_number})</span>
                            `;
                            assetListDiv.appendChild(label);
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