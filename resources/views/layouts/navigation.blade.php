<nav x-data="{ open: false }" class="bg-gray-800 border-b border-gray-700">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 relative">
            <div class="flex">
                <div class="shrink-0 flex items-center absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 md:static md:transform-none md:left-auto md:top-auto md:mr-4">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/partify.png') }}" 
                            alt="Partify Logo" 
                            class="h-16 md:h-16 w-auto object-contain mix-blend-screen">
                    </a>
                </div>

                <div class="hidden sm:space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('events.feed')" :active="request()->routeIs('events.feed')">
                        🏠 Böngészés
                    </x-nav-link>

                    @if(auth()->id() === 1)
                        <x-nav-link :href="route('admin.pages.index')" :active="request()->routeIs('admin.pages.*')">
                            <i class="fas fa-database mr-2"></i> {{ __('Helyszínek Kezelése') }}
                        </x-nav-link>
                    @endif
                    
                    @auth
                        <x-nav-link :href="route('events.my')" :active="request()->routeIs('events.my')">
                            🎫 Eseményeim
                        </x-nav-link>

                        <x-nav-link :href="route('events.create')" :active="request()->routeIs('events.create')" class="text-blue-400 font-bold">
                            ➕ Új Buli
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-400 bg-gray-800 hover:text-gray-300 focus:outline-none transition">
                                <div class="mr-3">
                                    @if(Auth::user()->avatar)
                                        <img class="h-8 w-8 rounded-full object-cover border border-gray-600" src="{{ Auth::user()->avatar }}" />
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-700 flex items-center justify-center text-xs border border-gray-600 text-white">👤</div>
                                    @endif
                                </div>
                                <div>{{ Auth::user()->name }}</div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profil szerkesztése</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Kijelentkezés
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm font-medium">Belépés</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold">Regisztráció</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-800 flex justify-around items-center h-16 z-50 shadow-[0_-5px_15px_rgba(0,0,0,0.5)]">
        
        <a href="{{ route('events.feed') }}" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-blue-400 {{ request()->routeIs('events.feed') ? 'text-blue-500' : '' }}">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-bold">Kezdőlap</span>
        </a>

        @auth
            <a href="{{ route('events.my') }}" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-blue-400 {{ request()->routeIs('events.my') ? 'text-blue-500' : '' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                <span class="text-[10px] font-bold">Jegyeim</span>
            </a>

            <a href="{{ route('events.create') }}" class="flex flex-col items-center justify-center w-full h-full -mt-5">
                <div class="bg-blue-600 rounded-full p-3 shadow-lg border-4 border-gray-900 transform transition active:scale-95">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </a>

            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-blue-400 {{ request()->routeIs('profile.edit') ? 'text-blue-500' : '' }}">
                <div class="mb-1">
                    @if(Auth::user()->avatar)
                        <img class="h-6 w-6 rounded-full object-cover border border-gray-600" src="{{ Auth::user()->avatar }}" />
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    @endif
                </div>
                <span class="text-[10px] font-bold">Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-white">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                <span class="text-[10px] font-bold">Belépés</span>
            </a>
        @endauth

    </div>
</nav>