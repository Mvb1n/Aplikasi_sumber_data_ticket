@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="__('Nama Aset')" />
        <x-text-input type="text" name="name" id="name" required class="mt-1 block w-full" :value="old('name', $asset->name ?? '')" />
    </div>
    <div>
        <x-input-label for="serial_number" :value="__('Nomor Seri (Tidak bisa diubah)')" />
        {{-- Tambahkan readonly, disabled, dan class untuk membuatnya terlihat non-aktif --}}
        <x-text-input type="text" name="serial_number" id="serial_number" required 
                    class="mt-1 block w-full bg-gray-100 border-gray-300" 
                    :value="old('serial_number', $asset->serial_number ?? '')" 
                    readonly disabled />
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
