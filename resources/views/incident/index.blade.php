<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Laporan Insiden') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('incidents.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Lapor Insiden Baru
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelapor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Site</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Lapor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>

                                <th class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($incidents as $incident)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $incident->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $incident->reporter_email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $incident->site_name ?? $incident->site_location_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $incident->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($incident->status == 'Open') bg-red-100 text-red-800 @endif
                                            @if($incident->status == 'In Progress') bg-yellow-100 text-yellow-800 @endif
                                            @if($incident->status == 'Resolved') bg-blue-100 text-blue-800 @endif
                                            @if($incident->status == 'Closed') bg-green-100 text-green-800 @endif
                                            @if($incident->status == 'Cancelled') bg-gray-100 text-gray-800-800 @endif
                                        ">
                                            {{ $incident->status }}
                                        </span>
                                    </td>

                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($incident->involved_asset_sn)
                                            <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-800 font-mono text-xs">
                                                {{ $incident->involved_asset_sn }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded bg-gray-50 text-gray-400 text-xs">
                                                Tidak ada aset terkait
                                            </span>
                                        @endif
                                    </td> --}}

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @if($incident->status == 'Open')
                                            <a href="{{ route('incidents.edit', $incident->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <a href="{{ route('incidents.show', $incident->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                            {{-- Form untuk menghapus laporan --}}
                                            <form action="{{ route('incidents.destroy', $incident->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin membatalkan laporan ini? Status aset terkait akan dikembalikan.');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Batalkan</button>
                                            </form>
                                        @else
                                            <a href="{{ route('incidents.show', $incident->id) }}" class="text-blue-600 hover:text-blue-900">Detail</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada laporan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>