<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Profil Információk') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            {{ __("Frissítsd a fiókod adatait és a profilképedet.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-center space-x-6">
            <div class="shrink-0">
                @if(Auth::user()->avatar)
                    <img class="h-24 w-24 object-cover rounded-full border-4 border-gray-700" src="{{ Auth::user()->avatar }}" alt="Current profile photo" />
                @else
                    <div class="h-24 w-24 rounded-full bg-gray-700 flex items-center justify-center text-4xl border-4 border-gray-600">
                        👤
                    </div>
                @endif
            </div>
            <label class="block">
                <span class="sr-only">Válassz profilképet</span>
                <input type="file" name="avatar" class="block w-full text-sm text-gray-400
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-full file:border-0
                  file:text-sm file:font-semibold
                  file:bg-blue-600 file:text-white
                  hover:file:bg-blue-700
                "/>
            </label>
        </div>

        <div>
            <x-input-label for="name" :value="__('Név')" class="text-gray-300" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-900 text-white border-gray-700 focus:border-blue-500 focus:ring-blue-500" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-300" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-900 text-white border-gray-700 focus:border-blue-500 focus:ring-blue-500" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4 mt-8">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                💾 Mentés
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400 font-bold"
                >{{ __('Sikerült!') }}</p>
            @endif
        </div>
    </form>
</section>