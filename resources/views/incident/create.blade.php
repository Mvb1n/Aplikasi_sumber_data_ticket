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

                        <div
                            x-data="{
                                isDragging: false,
                                fileList: []
                            }"
                            class="w-full"
                        >
                            <x-input-label for="attachments" :value="__('Foto/File Pendukung (Bisa lebih dari satu)')" />
                            
                            <label 
                                for="attachments"
                                class="relative mt-1 flex flex-col justify-center items-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 transition-colors duration-200 ease-in-out hover:border-gray-400"
                                :class="{ 'border-indigo-600 bg-indigo-50': isDragging }"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="
                                    isDragging = false;
                                    $refs.fileInput.files = $event.dataTransfer.files;
                                    $refs.fileInput.dispatchEvent(new Event('change'));
                                "
                            >
                                <div class="text-center space-y-2 pointer-events-none">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <span class="relative font-medium text-indigo-600 hover:text-indigo-500">Upload file</span>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, PDF, dll (Maks 5MB)</p>
                                </div>
                            </label>

                            <input 
                                type="file" 
                                name="attachments[]" 
                                id="attachments" 
                                multiple 
                                class="hidden"
                                x-ref="fileInput"
                                @change="
                                    let files = Array.from($event.target.files);
                                    fileList = files.map(file => file.name);
                                "
                            />

                            <div x-show="fileList.length > 0" class="mt-3 space-y-1" x-cloak>
                                <p class="font-medium text-sm text-gray-700">File yang dipilih:</p>
                                
                                <ul class="list-disc list-inside text-sm text-gray-600">
                                    <template x-for="fileName in fileList" :key="fileName">
                                        <li x-text="fileName" class="truncate"></li>
                                    </template>
                                </ul>
                            </div>
                            @error('attachments')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('attachments.*') 
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                const apiToken = '1|IQr8L4j8OcmlKhtjGu5bQePz6sOGV8dnuR2Vm0og84164778';

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