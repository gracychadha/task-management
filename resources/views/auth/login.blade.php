<x-guest-layout>

    <div class="relative min-h-screen flex items-center justify-center overflow-hidden px-4 sm:px-0">

        <!-- BACKGROUND IMAGE -->
        <img
            src="{{ asset('image/auth.jpg') }}"
            alt="Task Management"
            class="absolute inset-0 w-full h-full object-cover" />

        <!-- BACKGROUND OVERLAY -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-black/60"></div>

        <!-- LOGIN FORM -->
        <div class="relative z-10 w-full max-w-md">

            <!-- LOGIN CARD -->
            <div class="bg-white
                rounded-3xl
                shadow-[0_25px_80px_-15px_rgba(0,0,0,0.55)]
                border border-white/40
                overflow-hidden">

                <!-- TOP ACCENT BAR -->
                <div class="h-1.5 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-500"></div>

                <!-- MAIN FORM CONTENT -->
                <div class="p-8 sm:p-10">

                    <!-- BRAND -->
                    <div class="flex flex-col items-center text-center">

                        <div class="relative">
                            <div class="w-20 h-20 rounded-2xl
                                bg-gradient-to-br from-indigo-50 to-violet-50
                                border border-indigo-100
                                ring-8 ring-indigo-50/70
                                shadow-inner
                                flex items-center justify-center">

                                <img
                                    src="{{ asset('image/final-logo.png') }}"
                                    alt="Task Management System"
                                    class="w-14 h-14 object-contain" />

                            </div>
                        </div>

                        <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.22em] text-indigo-600">
                            Task Management System
                        </p>

                    </div>

                    <!-- HEADING -->
                    <div class="text-center mt-4 mb-8">

                        <h1 class="text-[26px] font-bold tracking-tight
                            bg-gradient-to-r from-slate-900 to-slate-600 bg-clip-text text-transparent">
                            Welcome back
                        </h1>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Sign in to access your task management workspace.
                        </p>

                    </div>

                    <!-- FORM -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">

                        @csrf

                        <!-- EMAIL -->
                        <div>
                            <x-input-label for="email" :value="__('Email address')"
                                class="text-sm font-semibold text-slate-700" />

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </span>

                                <x-text-input
                                    id="email"
                                    class="block w-full h-12 pl-11 pr-4 rounded-xl
                                        border-slate-300
                                        bg-slate-50
                                        text-slate-900
                                        placeholder:text-slate-400
                                        focus:bg-white
                                        focus:border-indigo-500
                                        focus:ring-2
                                        focus:ring-indigo-500/20
                                        transition"
                                    type="email"
                                    name="email"
                                    :value="old('email')"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="name@company.com" />
                            </div>

                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- PASSWORD -->
                        <div>
                            <div class="flex items-center justify-between">
                                <x-input-label for="password" :value="__('Password')"
                                    class="text-sm font-semibold text-slate-700" />

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                </span>

                                <x-text-input
                                    id="password"
                                    class="block w-full h-12 pl-11 pr-12 rounded-xl
                                        border-slate-300
                                        bg-slate-50
                                        text-slate-900
                                        placeholder:text-slate-400
                                        focus:bg-white
                                        focus:border-indigo-500
                                        focus:ring-2
                                        focus:ring-indigo-500/20
                                        transition"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password" />

                                <button type="button" id="toggle_password"
                                    class="absolute inset-y-0 right-3 flex items-center p-1 text-slate-400 hover:text-slate-600 transition">
                                    <span id="eye_open">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <span id="eye_closed" class="hidden">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </span>
                                </button>
                            </div>

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- REMEMBER ME -->
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="flex items-center cursor-pointer select-none">
                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    name="remember"
                                    class="w-4 h-4 rounded
                                        border-slate-300
                                        text-indigo-600
                                        focus:ring-indigo-500
                                        cursor-pointer" />

                                <span class="ml-2.5 text-sm text-slate-600">
                                    {{ __('Remember me') }}
                                </span>
                            </label>
                        </div>

                        <!-- LOGIN BUTTON -->
                        <button
                            type="submit"
                            class="group w-full h-12 rounded-xl
                                bg-gradient-to-r from-indigo-600 to-violet-600
                                hover:from-indigo-700 hover:to-violet-700
                                active:from-indigo-800 active:to-violet-800
                                text-white
                                font-semibold
                                shadow-lg shadow-indigo-600/25
                                hover:shadow-xl hover:shadow-indigo-600/30
                                transition-all duration-200
                                focus:outline-none
                                focus:ring-2
                                focus:ring-indigo-500
                                focus:ring-offset-2">

                            <span class="flex items-center justify-center gap-2">
                                {{ __('Log in') }}
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>

                        </button>

                    </form>

                </div>

                <!-- SECURITY FOOTER -->
                <div class="px-8 py-4
                    bg-gradient-to-r from-slate-50 to-indigo-50/40
                    border-t border-slate-100">

                    <div class="flex items-center justify-center gap-2 text-sm text-slate-500">
                        <svg class="w-4 h-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>Secure access to your team workspace</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('toggle_password');
            if (!toggle) {
                return;
            }

            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eye_open');
            const eyeClosed = document.getElementById('eye_closed');

            toggle.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', show);
                eyeClosed.classList.toggle('hidden', !show);
            });
        });
    </script>

</x-guest-layout>