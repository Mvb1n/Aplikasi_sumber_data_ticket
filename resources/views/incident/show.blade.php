<x-app-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if($incidentData)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8 border-b border-gray-200">
                        {{-- Bagian Header: Judul dan Status --}}
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                            <h1 class="text-3xl font-bold text-gray-900 leading-tight mb-2 sm:mb-0">
                                {{ $incidentData['title'] }}
                            </h1>
                            
                            {{-- Badge Status Dinamis --}}
                            @php
                                $status = $incidentData['status'];
                                $statusClasses = [
                                    'Open' => 'bg-red-100 text-red-800',
                                    'In Progress' => 'bg-yellow-100 text-yellow-800',
                                    'Resolved' => 'bg-blue-100 text-blue-800',
                                    'Closed' => 'bg-green-100 text-green-800',
                                    'Cancelled' => 'bg-gray-100 text-gray-800',
                                ];
                                $badgeClass = $statusClasses[$status] ?? $statusClasses['default'];
                            @endphp

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }}">
                                {{ $status }}
                            </span>
                        </div>

                        {{-- Detail Laporan --}}
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-4 text-sm text-gray-600">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>Dilaporkan oleh: <strong class="font-semibold text-gray-800">{{ $incidentData['user']['name'] }}</strong></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l-4 4-4-4-4 4"></path></svg>
                                <span>Ditugaskan kepada: <strong class="font-semibold text-gray-800">{{ $incidentData['assigned_to']['name'] ?? 'Belum Ditugaskan' }}</strong></span>
                            </div>
                            
                            {{-- PENAMBAHAN FIELD SITE --}}
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span>Lokasi Site: <strong class="font-semibold text-gray-800">{{ $incidentData['site']['name'] ?? 'Tidak diketahui' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Aset dan Komentar --}}
                    <div class="p-6 sm:p-8 space-y-8">
                        {{-- Daftar Aset --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Aset Terlibat</h3>
                             @if(!empty($incidentData['assets']) && count($incidentData['assets']) > 0)
                                <ul class="space-y-2">
                                    @foreach($incidentData['assets'] as $asset)
                                        <li class="p-3 bg-gray-50 rounded-md border border-gray-200 flex justify-between items-center">
                                            <span class="text-gray-800">{{ $asset['name'] ?? 'Aset Tidak Diketahui' }}</span>
                                            <span class="text-xs font-mono bg-gray-200 text-gray-600 px-2 py-1 rounded">
                                                SN: {{ $asset['serial_number'] ?? 'N/A' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 italic">Tidak ada aset yang terlibat dalam laporan ini.</p>
                            @endif
                        </div>

                        {{-- Daftar Komentar --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Komentar</h3>
                            <div class="space-y-6">
                                @forelse($incidentData['comments'] as $comment)
                                    <div class="flex items-start space-x-4">
                                        {{-- Avatar Placeholder --}}
                                        <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <span class="text-gray-500 font-bold">{{ strtoupper(substr($comment['user']['name'], 0, 1)) }}</span>
                                        </div>
                                        <div class="flex-grow">
                                            <p class="font-semibold text-gray-900">{{ $comment['user']['name'] }}</p>
                                            <div class="mt-1 p-3 bg-gray-100 rounded-lg rounded-tl-none">
                                                <p class="text-gray-700">{{ $comment['body'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 italic">Belum ada komentar.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-600">
                        <p>Gagal memuat data laporan. Silakan coba lagi nanti.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>