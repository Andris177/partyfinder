<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div x-data="eventFinder()" class="relative min-h-screen">
        
        <div class="relative w-full transition-all duration-500 ease-in-out border-b border-gray-700 shadow-2xl z-10"
             :class="mapExpanded ? 'h-[85vh]' : 'h-64 sm:h-80'">
            
            <div id="map" class="w-full h-full bg-gray-900 z-0"></div>

            <button @click="toggleMap()" 
                    class="absolute top-4 right-4 z-[500] bg-gray-800 hover:bg-gray-700 text-white p-3 rounded-full shadow-lg border border-gray-600 transition transform hover:scale-105 group">
                <svg x-show="!mapExpanded" class="w-6 h-6 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                <svg x-show="mapExpanded" class="w-6 h-6 text-red-500 group-hover:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div x-show="!mapExpanded" class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-900 to-transparent pointer-events-none"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            {{-- 🟢 SZŰRŐSÁV (ÚJ 6 OSZLOPOS VERZIÓ) --}}
            <div class="bg-gray-800 rounded-xl shadow-lg p-5 mb-8 border border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    
                    {{-- 1. Keresés --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Keresés</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" x-model="filters.keyword" @input.debounce.500ms="fetchEvents()" 
                                   class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg pl-10 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500"
                                   placeholder="Buli neve...">
                        </div>
                    </div>

                    {{-- 2. Ország --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Ország</label>
                        <select x-model="filters.country_id" @change="filterCities()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Összes --</option>
                            <template x-for="country in countries" :key="country.id">
                                <option :value="country.id" x-text="country.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- 3. Város --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Város</label>
                        <select x-model="filters.city_id" @change="filterVenues()" :disabled="!filters.country_id" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                            <option value="">-- Válassz --</option>
                            <template x-for="city in cities" :key="city.id">
                                <option :value="city.id" x-text="city.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- 4. Helyszín --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Helyszín</label>
                        <select x-model="filters.location_id" @change="fetchEvents()" :disabled="!filters.city_id" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                            <option value="">-- Összes --</option>
                            <template x-for="venue in venues" :key="venue.id">
                                <option :value="venue.id" x-text="venue.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- 5. Dátum --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Mikor?</label>
                        <input type="date" x-model="filters.date" @change="fetchEvents()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 [color-scheme:dark]">
                    </div>

                    {{-- 6. Stílus és Korhatár (Egymás mellett) --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Stílus</label>
                            <select x-model="filters.genre" @change="fetchEvents()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="all">Mind</option>
                                <option value="Techno">Techno</option>
                                <option value="House">House</option>
                                <option value="Drum & Bass">DNB</option>
                                <option value="Hardstyle">Hardstyle</option>
                                <option value="EDM">EDM</option>
                                <option value="Trance">Trance</option>
                                <option value="R&B">R&B</option>
                                <option value="Hip-Hop">Hip-Hop</option>
                                <option value="Retro">Retro</option>
                                <option value="Egyéb">Egyéb</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Kor</label>
                            <select x-model="filters.age_limit" @change="fetchEvents()" class="w-full bg-gray-900 text-white border border-gray-600 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="all">Mind</option>
                                <option value="0">Nincs</option>
                                <option value="16">16+</option>
                                <option value="18">18+</option>
                                <option value="21">21+</option>
                            </select>
                        </div>
                    </div>

                </div>
                
                <div class="mt-4 flex items-center justify-between border-t border-gray-700 pt-4">
                    <button @click="getLocation()" class="text-sm text-blue-400 hover:text-blue-300 flex items-center font-medium transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Közeli bulik keresése (GPS)
                    </button>
                    <span class="text-xs text-green-400 font-bold animate-pulse" x-show="userLat">📍 Pozíció bemérve & Lista rendezve!</span>
                </div>
            </div>

            {{-- 🟢 ESEMÉNY KÁRTYÁK --}}
            <div id="events-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <template x-if="loading">
                    <div class="col-span-full text-center py-20">
                        <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
                        <p class="text-gray-400 mt-3">Éjszakai élet betöltése...</p>
                    </div>
                </template>

                <template x-for="event in events" :key="event.id">
                    <div class="bg-gray-800 rounded-2xl overflow-hidden shadow-lg border border-gray-700 hover:border-blue-500/50 transition duration-300 group flex flex-col h-full relative"
                         @mouseenter="highlightMarker(event.id)"
                         @mouseleave="resetMarker(event.id)">
                        
                        <div class="h-56 bg-gray-900 relative overflow-hidden">
                            <template x-if="event.image_url">
                                <img :src="event.image_url" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                            </template>
                            <template x-if="!event.image_url">
                                <div class="w-full h-full flex items-center justify-center bg-gray-900">
                                    <span class="text-4xl">🎉</span>
                                </div>
                            </template>
                            
                            <div class="absolute top-3 left-3 bg-gray-900/90 backdrop-blur border border-gray-600 rounded-lg px-3 py-1 text-center shadow-lg">
                                <div class="text-xs text-gray-400 uppercase font-bold" x-text="new Date(event.start_time).toLocaleDateString('hu-HU', {month:'short'})"></div>
                                <div class="text-xl font-bold text-white" x-text="new Date(event.start_time).getDate()"></div>
                            </div>
                            
                            <div class="absolute top-3 right-3 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded shadow-lg uppercase tracking-wide">
                                Party
                             </div>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="text-xl font-bold text-white mb-1 group-hover:text-blue-400 transition" x-text="event.title"></h3>
                            
                            <div class="flex items-center justify-between text-gray-400 text-sm mb-3">
                                <div class="flex items-center truncate">
                                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="truncate" x-text="event.location.name"></span>
                                </div>
                                
                                <div class="bg-gray-900 border border-gray-700 text-xs px-2 py-1 rounded-md text-blue-400 font-mono"
                                     x-show="event.distance !== undefined">
                                    📍 <span x-text="event.distance ? event.distance.toFixed(1) : ''"></span> km
                                </div>
                            </div>

                            <p class="text-gray-500 text-sm line-clamp-2 mb-4 flex-1" x-text="event.description || 'Nincs leírás megadva.'"></p>

                            <div class="pt-4 border-t border-gray-700 flex items-center justify-between mt-auto">
                                <div class="flex items-center text-sm font-medium">
                                    <span class="text-green-500 flex items-center mr-4" title="Ott leszek">
                                        <i class="fas fa-check-circle mr-1.5"></i> 
                                        <span x-text="event.going_count || 0"></span>
                                    </span>

                                    <span class="text-yellow-500 flex items-center" title="Érdekel">
                                        <i class="fas fa-star mr-1.5"></i> 
                                        <span x-text="event.interested_count || 0"></span>
                                    </span>
                                </div>

                                <a :href="'/events/' + event.id" class="text-white bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-lg text-sm font-bold transition shadow-lg shadow-blue-900/50">
                                    Részletek
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!loading && events.length === 0" class="text-center py-20 bg-gray-800 rounded-xl border border-gray-700">
                <div class="text-6xl mb-4">😔</div>
                <h3 class="text-xl font-bold text-white">Sajnos nincs találat.</h3>
                <p class="text-gray-400">Próbálj más várost vagy dátumot keresni!</p>
            </div>
        </div>
    </div>

    <style>
        /* Sötét naptár ikon javítása */
        ::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
        
        /* Marker Stílusok */
        .party-marker { display: flex; align-items: center; justify-content: center; }
        .pin { width: 14px; height: 14px; border-radius: 50%; background: #3b82f6; box-shadow: 0 0 0 2px #ffffff; position: relative; z-index: 2; }
        .pulse { background: rgba(59, 130, 246, 0.5); border-radius: 50%; height: 40px; width: 40px; position: absolute; z-index: 1; animation: pulsate 2s ease-out infinite; opacity: 0; }
        @keyframes pulsate { 0% { transform: scale(0.1); opacity: 0.0; } 50% { opacity: 1.0; } 100% { transform: scale(1.2); opacity: 0.0; } }
        .marker-highlight .pin { background: #ef4444; transform: scale(1.5); transition: all 0.3s; box-shadow: 0 0 10px #ef4444; }
        .marker-highlight .pulse { background: rgba(239, 68, 68, 0.4); animation: none; transform: scale(1.2); opacity: 1; }
        .leaflet-popup-content-wrapper { background: #1f2937; color: white; border: 1px solid #374151; }
        .leaflet-popup-tip { background: #1f2937; border: 1px solid #374151; }
        .leaflet-container { background: #111827; }
    </style>

    <script>
        function eventFinder() {
            return {
                events: [],
                countries: [],
                allCities: [],
                allVenues: [],
                cities: [],
                venues: [],
                loading: true,
                map: null,
                markers: [],
                mapExpanded: false,
                userLat: null,
                userLng: null,
                filters: { keyword: '', country_id: '', city_id: '', location_id: '', date: '', genre: 'all', age_limit: 'all' },

                init() {
                    setTimeout(() => { this.initMap(); }, 100);
                    this.fetchBaseData();
                    this.fetchEvents();
                },

                fetchBaseData() {
                    // API-kon lekérjük az országokat, városokat és klubokat a szűrőhöz
                    fetch('/api/countries').then(r => r.json()).then(d => this.countries = d);
                    fetch('/api/cities').then(r => r.json()).then(d => this.allCities = d);
                    fetch('/api/locations').then(r => r.json()).then(d => this.allVenues = d);
                },

                filterCities() {
                    this.filters.city_id = '';
                    this.filters.location_id = '';
                    this.venues = [];
                    if (this.filters.country_id) {
                        this.cities = this.allCities.filter(c => c.country_id == this.filters.country_id);
                    } else {
                        this.cities = [];
                    }
                    this.fetchEvents();
                },

                filterVenues() {
                    this.filters.location_id = '';
                    if (this.filters.city_id) {
                        this.venues = this.allVenues.filter(v => v.city_id == this.filters.city_id);
                    } else {
                        this.venues = [];
                    }
                    this.fetchEvents();
                },

                toggleMap() {
                    this.mapExpanded = !this.mapExpanded;
                    setTimeout(() => { if(this.map) this.map.invalidateSize(); }, 500);
                },

                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.userLat = position.coords.latitude;
                                this.userLng = position.coords.longitude;
                                this.calculateAndSortEvents();
                            },
                            (error) => { alert("Nem sikerült lekérni a pozíciót: " + error.message); }
                        );
                    } else { alert("A böngésző nem támogatja a helymeghatározást."); }
                },

                initMap() {
                    if(!document.getElementById('map')) return;
                    this.map = L.map('map').setView([47.4979, 19.0402], 13);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; CARTO', subdomains: 'abcd', maxZoom: 20
                    }).addTo(this.map);
                    setTimeout(() => { this.map.invalidateSize(); }, 500);
                },

                fetchEvents() {
                    this.loading = true;
                    const params = new URLSearchParams(this.filters).toString();

                    fetch(`/api/events/filter?${params}`)
                        .then(res => res.ok ? res.json() : [])
                        .then(data => {
                            this.events = data.data ? data.data : (Array.isArray(data) ? data : []);
                            this.updateMap();
                            if (this.userLat && this.userLng) this.calculateAndSortEvents();
                            this.loading = false;
                        })
                        .catch(err => { console.error(err); this.loading = false; this.events = []; });
                },

                calculateAndSortEvents() {
                    if (!this.userLat || !this.events.length) return;
                    this.events = this.events.map(event => {
                        if (event.location && event.location.lat) {
                            event.distance = this.getDistanceFromLatLonInKm(
                                this.userLat, this.userLng, parseFloat(event.location.lat), parseFloat(event.location.lng)
                            );
                        }
                        return event;
                    });
                    this.events.sort((a, b) => {
                        const distA = a.distance !== undefined ? a.distance : 99999;
                        const distB = b.distance !== undefined ? b.distance : 99999;
                        return distA - distB;
                    });
                },

                getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
                    var R = 6371; 
                    var dLat = this.deg2rad(lat2-lat1);  
                    var dLon = this.deg2rad(lon2-lon1); 
                    var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) * Math.sin(dLon/2) * Math.sin(dLon/2); 
                    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
                    return R * c;
                },

                deg2rad(deg) { return deg * (Math.PI/180); },

                updateMap() {
                    if(!this.map) return;
                    this.map.invalidateSize();
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];
                    const bounds = L.latLngBounds();

                    this.events.forEach(event => {
                        if (event.location && event.location.lat) {
                            const lat = parseFloat(event.location.lat);
                            const lng = parseFloat(event.location.lng);

                            const customIcon = L.divIcon({
                                className: 'party-marker',
                                html: `<div class="pulse"></div><div class="pin"></div>`,
                                iconSize: [40, 40], iconAnchor: [20, 20], popupAnchor: [0, -20]
                            });

                            const marker = L.marker([lat, lng], { icon: customIcon })
                                .addTo(this.map)
                                .bindPopup(`
                                    <div class="text-center font-sans">
                                        <b class="text-lg text-blue-400">${event.title}</b><br>
                                        <span class="text-gray-300 text-sm">${event.location.name}</span><br>
                                        <a href="/events/${event.id}" class="mt-2 inline-block text-white bg-blue-600 px-3 py-1 rounded text-xs font-bold">Részletek</a>
                                    </div>
                                `);
                            
                            marker.eventId = event.id;
                            marker.on('add', function(){ marker._icon.id = 'marker-' + event.id; });
                            this.markers.push(marker);
                            bounds.extend([lat, lng]);
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