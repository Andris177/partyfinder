<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partify - Regisztráció</title>
    <link rel="icon" type="image/png" href="{{ asset('images/partify.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="max-w-md w-full">
        <div class="text-center mb-8 flex flex-col items-center">
            <a href="/" class="flex flex-col items-center">
                <img src="{{ asset('images/partify.png') }}" alt="Partify Logo" class="h-20 w-auto mb-3 drop-shadow-lg transition-transform hover:scale-105">
                
                <span class="inline-block text-4xl font-extrabold text-blue-500 tracking-wider">
                    <span class="text-white">PART</span>IFY
                </span>
            </a>
        </div>

        <div class="bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Hozd létre a fiókod</h2>
                <p class="text-gray-400 text-sm">Csatlakozz és találd meg a legjobb bulikat!</p>
            </div>

            <div class="space-y-4 mb-8">
                <a href="{{ route('social.redirect', 'google') }}" class="w-full flex items-center justify-center bg-white hover:bg-gray-100 text-gray-900 font-semibold py-2.5 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    Regisztráció Google-el
                </a>
                <a href="{{ route('social.redirect', 'apple') }}" class="w-full flex items-center justify-center bg-black hover:bg-gray-900 text-white border border-gray-700 font-semibold py-2.5 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.04 2.34-.85 3.73-.78 1.65.11 2.88.75 3.65 1.9-3.08 1.83-2.58 5.71.39 6.87-.78 1.83-1.66 3.38-2.85 4.18zM12.03 7.21c-.15-2.91 2.39-5.32 5.16-5.21.31 3.05-2.66 5.46-5.16 5.21z"/></svg>
                    Regisztráció Apple-el
                </a>
            </div>

            <div class="flex items-center mb-6">
                <div class="flex-grow border-t border-gray-600"></div>
                <span class="px-3 text-xs text-gray-500 uppercase tracking-wide">Vagy hagyományos mód</span>
                <div class="flex-grow border-t border-gray-600"></div>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Teljes Név</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email cím</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Jelszó</label>
                    <input id="password" type="password" name="password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">Jelszó megerősítése</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-lg shadow-blue-500/30">
                    Regisztráció
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-400">
                Már van fiókod? 
                <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium transition">Lépj be itt</a>
            </p>
        </div>
    </div>
</body>
</html>