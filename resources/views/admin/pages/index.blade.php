<x-app-layout>
    <div class="py-12 bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white">Kezelt Szórakozóhelyek ({{ $pages->total() }})</h2>
                <a href="{{ route('admin.pages.create') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Új Hely Felvétele
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-700 text-gray-300 text-sm uppercase tracking-wider">
                                <th class="p-4 font-bold border-b border-gray-600">Név</th>
                                <th class="p-4 font-bold border-b border-gray-600">Város</th>
                                <th class="p-4 font-bold border-b border-gray-600">Facebook Link</th>
                                <th class="p-4 font-bold border-b border-gray-600 text-right">Műveletek</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-300 divide-y divide-gray-700">
                            @foreach($pages as $page)
                                <tr class="hover:bg-gray-700/50 transition">
                                    <td class="p-4 font-bold text-white">{{ $page->name }}</td>
                                    <td class="p-4">
                                        <span class="bg-gray-600 text-white text-xs px-2 py-1 rounded-full">
                                            {{ $page->city->name ?? 'Ismeretlen' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <a href="{{ $page->url }}" target="_blank" class="text-blue-400 hover:text-blue-300 hover:underline flex items-center">
                                            <i class="fab fa-facebook mr-2"></i> Megnyitás
                                        </a>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end items-center gap-3">
                                            
                                            {{-- ✏️ SZERKESZTÉS GOMB (ITT AZ ÚJ RÉSZ) --}}
                                            <a href="{{ route('admin.locations.edit', $page->id) }}" class="text-yellow-400 hover:text-yellow-300 transition flex items-center" title="Szerkesztés">
                                                <i class="fas fa-edit mr-1"></i> Szerkesztés
                                            </a>

                                            {{-- 🗑️ TÖRLÉS GOMB --}}
                                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" onsubmit="return confirm('Biztosan törölni akarod ezt a helyet?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 transition flex items-center" title="Törlés">
                                                    <i class="fas fa-trash-alt mr-1"></i> Törlés
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-700">
                    {{ $pages->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>