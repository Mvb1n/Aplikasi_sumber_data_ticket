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
                    @include('incident.partials.form')
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
                const apiToken = '2|Z2PIDGYnVHlqip1FWw1WZWZJrTHG4EjXbf3wGZyA7ce09e7f';

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