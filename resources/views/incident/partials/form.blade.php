@if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
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
