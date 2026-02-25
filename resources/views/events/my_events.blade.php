<x-app-layout>
    <div class="py-12 bg-gray-900 min-h-screen text-white">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-calendar-check mr-3 text-blue-500"></i>
                    Eseményeim
                </h1>
                <a href="/" class="text-gray-400 hover:text-white transition">← Vissza a térképhez</a>
            </div>

            <div class="mb-8 border-b border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <span class="border-b-2 border-green-500 py-4 px-1 text-sm font-medium text-green-400">
                        Visszajelzéseim
                    </span>
                </nav>
            </div>

            @if($events->isEmpty())
                <div class="text-center py-20 bg-gray-800 rounded-2xl border border-gray-700">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-xl font-bold text-white">Még nem jelöltél be semmit.</h3>
                    <p class="text-gray-400 mt-2">Nézz szét a főoldalon!</p>
                    <a href="/" class="mt-6 inline-block bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-lg font-bold transition">
                        Bulik keresése
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($events as $event)
                        <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700 hover:border-blue-500/50 transition group h-full flex flex-col">
                            
                            <div class="relative h-48 bg-gray-900">
                                @if($event->image_url)
                                    <img src="{{ $event->image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-4xl">🎉</div>
                                @endif
                                
                                <div class="absolute top-3 left-3 bg-gray-900/90 backdrop-blur px-3 py-1 rounded-lg text-center border border-gray-600">
                                    <div class="text-xs text-gray-400 uppercase font-bold">{{ $event->start_time->translatedFormat('M') }}</div>
                                    <div class="text-lg font-bold text-white">{{ $event->start_time->format('d') }}</div>
                                </div>

                                <div class="absolute top-3 right-3">
                                    @php
                                        // Most már a betöltött 'reactions' kapcsolatból vesszük, nem queryzünk
                                        $myReaction = $event->reactions->first()->type ?? '';
                                    @endphp
                                    
                                    @if($myReaction == 'going')
                                        <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded uppercase shadow-lg flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i> Ott leszek
                                        </span>
                                    @elseif($myReaction == 'interested')
                                        <span class="bg-yellow-600 text-white text-xs font-bold px-2 py-1 rounded uppercase shadow-lg flex items-center">
                                            <i class="fas fa-star mr-1"></i> Érdekel
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="text-xl font-bold text-white mb-1 leading-tight">{{ $event->title }}</h3>
                                
                                <div class="text-gray-400 text-sm mb-4 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    {{ $event->location->name ?? 'Helyszín' }}
                                    <span class="mx-2">|</span>
                                    {{ $event->start_time->format('H:i') }}
                                </div>

                                <div class="mt-auto pt-4 border-t border-gray-700 flex items-center justify-between">
                                    <div class="flex items-center space-x-4 text-sm font-medium">
                                        <span class="text-yellow-500 flex items-center" title="Érdekel">
                                            <i class="fas fa-star mr-1.5"></i> {{ $event->interested_count }}
                                        </span>
                                    </div>

                                    <a href="{{ route('events.show', $event->id) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-sm font-bold transition">
                                        Megnyitás
                                    </a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>