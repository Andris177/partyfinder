<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div x-data="eventFinder()" class="relative min-h-screen bg-gray-900 text-white">
        
        <div class="relative w-full transition-all duration-500 ease-in-out border-b border-gray-700 shadow-2xl z-10"
             :class="mapExpanded ? 'h-[85vh]' : 'h-64 sm:h-80'">
            <div id="map" class="w-full h-full bg-gray-900 z-0"></div>
            <button @click="toggleMap()" 
                    class="absolute top-4 right-4 z-[500] bg-gray-800 hover:bg-gray-700 text-white p-3 rounded-full shadow-lg border border-gray-600 transition transform hover:scale-105 group">
                <i class="fas fa-map w-6 h-6 flex items-center justify-center text-blue-500 group-hover:text-blue-400" x-show="!mapExpanded"></i>
                <i class="fas fa-times w-6 h-6 flex items-center justify-center text-red-500 group-hover:text-red-400" x-show="mapExpanded"></i>
            </button>
            <div x-show="!mapExpanded" class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-900 to-transparent pointer-events-none"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="bg-gray-800 rounded-xl shadow-lg p-5 mb-8 border border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="col-span-1 md:col-span-1">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Keresés</label>
                        <input type="text" x-model="filters.keyword" @input.debounce.500ms="fetchEvents()" 
                               class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-3 py-2" placeholder="Buli neve...">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Város</label>
                        <select x-model="filters.city_id" @change="fetchVenues(); fetchEvents()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-3 py-2">
                            <option value="">-- Mindenhol --</option>
                            <template x-for="city in cities" :key="city.id"><option :value="city.id" x-text="city.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Helyszín</label>
                        <select x-model="filters.location_id" @change="fetchEvents()" :disabled="!filters.city_id" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-3 py-2 disabled:opacity-50">
                            <option value="">-- Összes --</option>
                            <template x-for="venue in venues" :key="venue.id"><option :value="venue.id" x-text="venue.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Stílus</label>
                        <select x-model="filters.genre" @change="fetchEvents()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-3 py-2">
                            <option value="all">-- Összes --</option><option value="R&B">R&B</option><option value="Hip-Hop">Hip-Hop</option><option value="Techno">Techno</option><option value="Egyéb">Egyéb</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Korhatár</label>
                        <select x-model="filters.age_limit" @change="fetchEvents()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-3 py-2">
                            <option value="all">-- Mindegy --</option><option value="0">Nincs</option><option value="16">16+</option><option value="18">18+</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-700">
                    <button @click="getLocation()" class="text-sm text-blue-400 hover:text-blue-300 flex items-center font-medium">
                        <i class="fas fa-location-arrow mr-2"></i> Közeli bulik (GPS)
                    </button>
                </div>
            </div>

            <div id="events-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-if="loading">
                    <div class="col-span-full text-center py-20 text-gray-400">Betöltés...</div>
                </template>

                <template x-for="event in events" :key="event.id">
                    <div class="bg-gray-800 rounded-2xl overflow-hidden shadow-lg border border-gray-700 hover:border-blue-500/50 transition duration-300 group flex flex-col h-full relative"
                         @mouseenter="highlightMarker(event.id)"
                         @mouseleave="resetMarker(event.id)">
                        
                        <div class="h-56 bg-gray-900 relative overflow-hidden">
                            <template x-if="event.image_url">
                                <img :src="event.image_url" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700" referrerpolicy="no-referrer">
                            </template>
                            <template x-if="!event.image_url"><div class="w-full h-full flex items-center justify-center bg-gray-900 text-4xl">🎉</div></template>
                            
                            <div class="absolute top-3 left-3 bg-gray-900/90 backdrop-blur border border-gray-600 rounded-lg px-3 py-1 text-center shadow-lg">
                                <div class="text-xs text-gray-400 uppercase font-bold" x-text="event.fix_month_name"></div>
                                <div class="text-xl font-bold text-white" x-text="event.fix_day"></div>
                            </div>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="text-xl font-bold text-white mb-1 group-hover:text-blue-400 transition" x-text="event.title"></h3>
                            <div class="flex items-center justify-between text-gray-400 text-sm mb-3">
                                <span class="truncate"><i class="fas fa-map-marker-alt mr-2"></i><span x-text="event.location.name"></span></span>
                                <div x-show="event.distance" class="bg-gray-900 border border-gray-700 px-2 py-1 rounded text-blue-400">
                                    📍 <span x-text="event.distance ? event.distance.toFixed(1) : ''"></span> km
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-700 flex items-center justify-between mt-auto">
                                <div class="flex items-center text-sm font-medium">
                                    <span class="text-yellow-500 flex items-center"><i class="fas fa-star mr-1.5"></i> <span x-text="event.interested_count || 0"></span></span>
                                </div>
                                <a :href="'/events/' + event.id" class="text-white bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-lg text-sm font-bold transition">Részletek</a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <style>
        .party-marker { display: flex; align-items: center; justify-content: center; }
        .pin { width: 14px; height: 14px; border-radius: 50%; background: #3b82f6; box-shadow: 0 0 0 2px #ffffff; position: relative; z-index: 2; }
        .pulse { background: rgba(59, 130, 246, 0.5); border-radius: 50%; height: 40px; width: 40px; position: absolute; animation: pulsate 2s infinite; }
        @keyframes pulsate { 0% { transform: scale(0.1); opacity: 0.0; } 50% { opacity: 1.0; } 100% { transform: scale(1.2); opacity: 0.0; } }
        .marker-highlight .pin { background: #ef4444; transform: scale(1.5); transition: all 0.3s; }
        .leaflet-popup-content-wrapper { background: #1f2937; color: white; border: 1px solid #374151; }
        .leaflet-popup-tip { background: #1f2937; border: 1px solid #374151; }
    </style>

    <script>
        function eventFinder() {
            return {
                events: [], cities: [], venues: [], loading: true, map: null, markers: [], mapExpanded: false, userLat: null, userLng: null,
                filters: { keyword: '', city_id: '', location_id: '', genre: 'all', age_limit: 'all' },

                init() { setTimeout(() => { this.initMap(); }, 100); this.fetchCities(); this.fetchEvents(); },
                toggleMap() { this.mapExpanded = !this.mapExpanded; setTimeout(() => { if(this.map) this.map.invalidateSize(); }, 500); },

                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((p) => { 
                            this.userLat = p.coords.latitude; this.userLng = p.coords.longitude; this.calculateAndSortEvents(); 
                        }, () => alert("Helymeghatározás sikertelen."));
                    }
                },

                initMap() {
                    if(!document.getElementById('map')) return;
                    this.map = L.map('map').setView([47.4979, 19.0402], 13);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; CARTO' }).addTo(this.map);
                },

                fetchCities() { fetch('/api/cities').then(r => r.json()).then(d => this.cities = d); },
                fetchVenues() { 
                    this.filters.location_id = ''; this.venues = []; 
                    if(this.filters.city_id) fetch(`/api/locations?city_id=${this.filters.city_id}`).then(r => r.json()).then(d => this.venues = d);
                },

                fetchEvents() {
                    this.loading = true; const p = new URLSearchParams(this.filters).toString();
                    fetch(`/api/events/filter?${p}`).then(r => r.json()).then(d => {
                        this.events = d; 
                        this.updateMap();
                        if(this.userLat) this.calculateAndSortEvents();
                        this.loading = false;
                    });
                },

                calculateAndSortEvents() {
                    if (!this.userLat || !this.events.length) return;
                    this.events = this.events.map(e => {
                        if (e.location && e.location.lat) {
                            e.distance = this.getDist(this.userLat, this.userLng, parseFloat(e.location.lat), parseFloat(e.location.lng));
                        }
                        return e;
                    });
                    this.events.sort((a, b) => (a.distance||9999) - (b.distance||9999));
                },
                getDist(lat1, lon1, lat2, lon2) {
                    var R = 6371, dLat = (lat2-lat1)*(Math.PI/180), dLon = (lon2-lon1)*(Math.PI/180);
                    var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat1*(Math.PI/180))*Math.cos(lat2*(Math.PI/180))*Math.sin(dLon/2)*Math.sin(dLon/2);
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                },

                updateMap() {
                    if(!this.map) return;
                    this.markers.forEach(m => this.map.removeLayer(m)); this.markers = [];
                    const bounds = L.latLngBounds();

                    this.events.forEach(event => {
                        if (event.location && event.location.lat) {
                            const icon = L.divIcon({ className: 'party-marker', html: `<div class="pulse"></div><div class="pin"></div>`, iconSize: [40, 40], iconAnchor: [20, 20] });
                            const marker = L.marker([event.location.lat, event.location.lng], { icon: icon }).addTo(this.map)
                                .bindPopup(`<div class="text-center"><b>${event.title}</b><br><span class="text-sm text-gray-300">${event.location.name}</span><br><a href="/events/${event.id}" class="text-blue-400 font-bold">Részletek</a></div>`);
                            
                            marker.eventId = event.id;
                            this.markers.push(marker); bounds.extend([event.location.lat, event.location.lng]);
                        }
                    });
                    if (this.markers.length > 0) this.map.fitBounds(bounds, { padding: [50, 50] });
                },

                highlightMarker(id) { 
                    const m = this.markers.find(x => x.eventId === id); 
                    if(m && m._icon) { m._icon.classList.add('marker-highlight'); m.openPopup(); } 
                },
                resetMarker(id) { 
                    const m = this.markers.find(x => x.eventId === id); 
                    if(m && m._icon) { m._icon.classList.remove('marker-highlight'); m.closePopup(); } 
                }
            }
        }
    </script>
</x-app-layout>