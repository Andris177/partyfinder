<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-2xl sm:rounded-2xl border border-gray-700">
                
                <div class="p-8 border-b border-gray-700 bg-gray-900">
                    <h2 class="text-2xl font-bold text-white">✨ Új Buli Létrehozása</h2>
                    <p class="text-gray-400 text-sm mt-1">Töltsd ki az adatokat és tedd közzé az eseményt!</p>
                </div>

                <div class="p-8">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-900/50 border border-red-500 text-red-200 p-4 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Esemény Címe</label>
                            <input type="text" name="title" required placeholder="Pl. Summer Vibes Techno Party"
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Borítókép</label>
                            <input type="file" name="image" accept="image/*"
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2 text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"/>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Helyszín</label>
                                <select name="location_id" required class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Válassz helyszínt...</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">
                                            {{ $location->name }} ({{ $location->city->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Kezdés Időpontja</label>
                                <input type="datetime-local" name="start_time" required
                                       class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500 [color-scheme:dark]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Leírás</label>
                            <textarea name="description" rows="4" placeholder="Írj valamit a buliról..."
                                      class="w-full bg-gray-900 border border-gray-600 rounded-lg p-3 text-white focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                                🚀 Esemény Közzététele
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>