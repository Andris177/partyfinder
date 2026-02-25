<x-app-layout>
    <div class="py-12 bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Címsor --}}
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-white">
                    Helyszín szerkesztése: <span class="text-blue-400">{{ $location->name }}</span>
                </h2>
            </div>

            {{-- Űrlap doboz --}}
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700 p-6">
                
                {{-- 🔴 ITT VOLT A HIBA! Most javítva: admin.locations.update --}}
                <form action="{{ route('admin.locations.update', $location->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- 1. Név --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Helyszín Neve</label>
                        <input type="text" name="name" id="name" 
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500" 
                               value="{{ $location->name }}" required>
                    </div>

                    {{-- 2. Város --}}
                    <div class="mb-4">
                        <label for="city_id" class="block text-sm font-medium text-gray-300 mb-2">Város</label>
                        <select name="city_id" id="city_id" 
                                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ $location->city_id == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Cím --}}
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-300 mb-2">Pontos Cím</label>
                        <input type="text" name="address" id="address" 
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500" 
                               value="{{ $location->address }}">
                        <p class="text-gray-500 text-xs mt-1">Pl: Riesenradplatz 7</p>
                    </div>

                    {{-- 4. Koordináták (Egymás mellett) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="lat" class="block text-sm font-medium text-gray-300 mb-2">Szélesség (Lat)</label>
                            <input type="text" name="lat" id="lat" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ $location->lat }}">
                        </div>
                        <div>
                            <label for="lng" class="block text-sm font-medium text-gray-300 mb-2">Hosszúság (Lng)</label>
                            <input type="text" name="lng" id="lng" 
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ $location->lng }}">
                        </div>
                    </div>

                    {{-- Gombok --}}
                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded-lg transition">
                            💾 Mentés
                        </button>
                        
                        {{-- Vissza gomb --}}
                        <a href="{{ route('admin.pages.index') }}" class="text-gray-400 hover:text-white transition">
                            Mégse
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>