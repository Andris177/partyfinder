<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <div class="min-h-screen bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <a href="/" class="inline-flex items-center text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i> Vissza a főoldalra
                </a>

                @if(auth()->check() && auth()->id() == $event->created_by)
                    <a href="{{ route('events.edit', $event->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition shadow-lg">
                        <i class="fas fa-edit mr-2"></i> Szerkesztés
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-gray-800 rounded-2xl overflow-hidden shadow-2xl border border-gray-700 relative">
                        <div class="h-64 sm:h-96 w-full relative">
                            @if($event->image_url)
                                <img src="{{ $event->image_url }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gray-700 flex items-center justify-center text-6xl">🎉</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>
                            
                            <div class="absolute top-4 left-4 bg-gray-900/90 backdrop-blur border border-gray-600 rounded-xl px-4 py-2 text-center shadow-lg">
                                <div class="text-sm text-gray-400 uppercase font-bold">{{ $event->fix_month_name }}</div>
                                <div class="text-3xl font-bold text-white">{{ $event->fix_day }}</div>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 -mt-12 relative z-10">
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2 leading-tight">{{ $event->title }}</h1>
                            <div class="flex flex-wrap items-center gap-4 text-gray-300 text-sm sm:text-base">
                                <span class="flex items-center"><i class="fas fa-map-marker-alt text-blue-500 mr-2"></i> {{ $event->location->name }}</span>
                                <span class="flex items-center"><i class="fas fa-clock text-blue-500 mr-2"></i> {{ $event->start_time->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl border border-gray-700">
                        <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2">Részletek</h2>
                        <div class="prose prose-invert max-w-none text-gray-300 whitespace-pre-line leading-relaxed">
                            {{ $event->description }}
                        </div>
                    </div>

                    <div class="block lg:hidden space-y-4">
                        @if($event->ticket_url)
                        <a href="{{ $event->ticket_url }}" target="_blank" class="block w-full text-center bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-4 rounded-xl shadow-lg transform hover:scale-[1.02] transition duration-300">
                            <i class="fas fa-ticket-alt mr-2"></i> Jegyvásárlás
                        </a>
                        @endif

                        <a href="{{ $event->facebook_url }}" target="_blank" class="block w-full text-center bg-[#1877F2] hover:bg-[#166fe5] text-white font-bold py-3 rounded-xl shadow-lg transition">
                            <i class="fab fa-facebook mr-2"></i> Facebook Esemény
                        </a>

                        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 shadow-lg">
                            <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-4 text-center">Visszajelzés</h3>
                            <div class="flex gap-3">
                                <form action="{{ route('events.react', $event->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="type" value="interested">
                                    <button type="submit" class="w-full py-3 rounded-lg font-bold transition flex flex-col items-center justify-center {{ $event->auth_reaction == 'interested' ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' : 'bg-gray-700 hover:bg-gray-600 text-gray-300' }}">
                                        <i class="fas fa-star mb-1 text-lg"></i>
                                        <span class="text-xs">Érdekel</span>
                                        <span class="text-xs opacity-75">({{ $event->interested_count }})</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl border border-gray-700">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <i class="fas fa-comments text-blue-500 mr-3"></i> Chat / Beszélgetés
                        </h2>

                        <div class="space-y-6 mb-8 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($event->comments as $comment)
                                <div class="flex gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                            {{ substr($comment->user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-gray-700/50 rounded-2xl rounded-tl-none px-4 py-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-bold text-white text-sm">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-300 text-sm">{{ $comment->content }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 py-4 italic">Még nincsenek hozzászólások. Légy te az első!</div>
                            @endforelse
                        </div>

                        @auth
                            <form action="{{ route('events.comment', $event->id) }}" method="POST" class="relative">
                                @csrf
                                <input type="text" name="content" placeholder="Írj valamit..." class="w-full bg-gray-900 border border-gray-600 rounded-full py-3 pl-5 pr-12 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <button type="submit" class="absolute right-2 top-2 w-8 h-8 bg-blue-600 hover:bg-blue-500 rounded-full flex items-center justify-center text-white transition">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                </button>
                            </form>
                        @else
                            <div class="text-center text-gray-400 bg-gray-900/50 rounded-lg p-3">
                                <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Jelentkezz be</a> a hozzászóláshoz!
                            </div>
                        @endauth
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">

                    <div class="hidden lg:block space-y-6">
                        @if($event->ticket_url)
                        <a href="{{ $event->ticket_url }}" target="_blank" class="block w-full text-center bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-4 rounded-xl shadow-lg transform hover:scale-[1.02] transition duration-300">
                            <i class="fas fa-ticket-alt mr-2"></i> Jegyvásárlás
                        </a>
                        @endif

                        <a href="{{ $event->facebook_url }}" target="_blank" class="block w-full text-center bg-[#1877F2] hover:bg-[#166fe5] text-white font-bold py-3 rounded-xl shadow-lg transition">
                            <i class="fab fa-facebook mr-2"></i> Facebook Esemény
                        </a>

                        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 shadow-lg">
                            <h3 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-4 text-center">Visszajelzés</h3>
                            <div class="flex gap-3">
                                <form action="{{ route('events.react', $event->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="type" value="interested">
                                    <button type="submit" class="w-full py-2 rounded-lg font-bold transition flex flex-col items-center justify-center {{ $event->auth_reaction == 'interested' ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' : 'bg-gray-700 hover:bg-gray-600 text-gray-300' }}">
                                        <i class="fas fa-star mb-1 text-lg"></i>
                                        <span class="text-xs">Érdekel</span>
                                        <span class="text-xs opacity-75">({{ $event->interested_count }})</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-xl overflow-hidden border border-gray-700 shadow-lg h-64 relative z-0">
                        <div id="detail-map" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('detail-map').setView([{{ $event->location->lat }}, {{ $event->location->lng }}], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; CARTO'
            }).addTo(map);

            var icon = L.divIcon({
                className: 'party-marker',
                html: `<div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>`,
                iconSize: [20, 20]
            });

            L.marker([{{ $event->location->lat }}, {{ $event->location->lng }}], {icon: icon})
                .addTo(map)
                .bindPopup("<b>{{ $event->location->name }}</b><br>{{ $event->location->address }}")
                .openPopup();
        });
    </script>
</x-app-layout>