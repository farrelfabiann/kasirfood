<x-guest-layout>
    <div>
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-600">Masuk Admin</p>
        <h2 class="mt-1.5 font-display text-2xl font-extrabold tracking-tight text-stone-900">Selamat datang kembali</h2>
        <p class="mt-1.5 text-sm leading-6 text-stone-500">Masuk untuk mengelola menu dan memantau pesanan kiosk.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="!text-xs !font-bold !text-stone-600" />
            <x-text-input
                id="email"
                class="input-field mt-1.5"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="nama@email.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 !text-xs" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="!text-xs !font-bold !text-stone-600" />

            <x-text-input
                id="password"
                class="input-field mt-1.5"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-1.5 !text-xs" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500" name="remember">
                <span class="text-xs font-semibold text-stone-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-bold text-brand-600 hover:text-brand-700" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full !py-3.5">
            Masuk
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>
</x-guest-layout>
