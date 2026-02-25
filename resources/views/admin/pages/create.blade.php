<x-app-layout>
    <div class="py-12 bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-700">
                
                <h2 class="text-2xl font-bold text-white mb-6">Új Szórakozóhely Hozzáadása</h2>

                <form action="{{ route('admin.pages.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-gray-300 text-sm font-bold mb-2">Helyszín Neve</label>
                        <input type="text" name="name" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 focus:ring-blue-500" placeholder="Pl. Fabric London" required>
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-bold mb-2">Facebook Oldal Linkje</label>
                        <input type="url" name="url" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 focus:ring-blue-500" placeholder="https://www.facebook.com/fabriclondon" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-300 text-sm font-bold mb-2">Ország</label>
                            <input type="text" 
                                   name="country_name" 
                                   list="countries_list" 
                                   class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 focus:ring-blue-500" 
                                   placeholder="Pl. United Kingdom" 
                                   required>
                            
                            <datalist id="countries_list">
                                @foreach($countries as $country)
                                    <option value="{{ $country->name }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-gray-300 text-sm font-bold mb-2">Város</label>
                            <input type="text" 
                                   name="city_name" 
                                   list="cities_list" 
                                   class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 focus:ring-blue-500" 
                                   placeholder="Pl. London" 
                                   required>

                            <datalist id="cities_list">
                                @foreach($cities as $city)
                                    <option value="{{ $city->name }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div class="bg-gray-700/50 p-4 rounded-lg border border-gray-600 text-sm text-gray-400">
                        <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                        Ha az ország vagy a város még nem létezik, a rendszer automatikusan létrehozza és összekapcsolja őket!
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg transition shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i> Hozzáadás a Rendszerhez
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>