<x-guest-layout>

    <div class="min-h-screen flex flex-col lg:flex-row bg-white">

        <!-- ========================================= -->
        <!-- LEFT SIDE - IMAGE -->
        <!-- ========================================= -->

        <div class="relative w-full lg:w-1/2 min-h-[300px] lg:min-h-screen overflow-hidden">

            <img
                src="{{ asset('image/auth.jpg') }}"
                alt="Task Management"
                class="absolute inset-0 w-full h-full object-cover" />

            <!-- Subtle Overlay -->
            <div class="absolute inset-0 bg-black/20"></div>

        </div>


        <!-- ========================================= -->
        <!-- RIGHT SIDE - LOGIN FORM -->
        <!-- ========================================= -->

        <div class="w-full lg:w-1/2 min-h-screen flex items-center justify-center bg-slate-50 px-6 py-10">

            <div class="w-full max-w-md">

                <!-- LOGO -->
                <div class="flex justify-center mb-6">

                    <img
                        src="{{ asset('image/tmc.png') }}"
                        alt="Task Management System"
                        class="w-24 h-24 object-contain rounded-2xl bg-white p-3 shadow-sm border border-slate-200" />

                </div>


                <!-- HEADING -->
                <div class="text-center mb-7">

                    <h1 class="text-3xl font-bold text-slate-900">
                        Welcome back
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Sign in to access your task management workspace.
                    </p>

                </div>


                <!-- SESSION STATUS -->
                <x-auth-session-status
                    class="mb-5"
                    :status="session('status')" />


                <!-- LOGIN CARD -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-7 sm:p-8">

                    <form
                        method="POST"
                        action="{{ route('login') }}"
                        class="space-y-5">

                        @csrf


                        <!-- EMAIL -->
                        <div>

                            <x-input-label
                                for="email"
                                :value="__('Email address')"
                                class="text-slate-700 font-medium" />

                            <x-text-input
                                id="email"
                                class="block w-full mt-3 py-3 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@company.com" />

                            <x-input-error
                                :messages="$errors->get('email')"
                                class="mt-2" />

                        </div>


                        <!-- PASSWORD -->
                        <div>

                            <div class="flex items-center justify-between">

                                <x-input-label
                                    for="password"
                                    :value="__('Password')"
                                    class="text-slate-700 font-medium" />

                                @if (Route::has('password.request'))

                                <a
                                    href="{{ route('password.request') }}"
                                    class="text-sm text-indigo-600 hover:text-indigo-700">
                                    Forgot password?
                                </a>

                                @endif

                            </div>

                            <x-text-input
                                id="password"
                                class="block w-full mt-3 py-3 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password" />

                            <x-input-error
                                :messages="$errors->get('password')"
                                class="mt-2" />

                        </div>


                        <!-- REMEMBER ME -->
                        <div class="pt-1 py-2">

                            <label
                                for="remember_me"
                                class="inline-flex items-center cursor-pointer">

                                <input
                                    id="remember_me"
                                    type="checkbox"
                                    name="remember"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">

                                <span class="ml-2.5 text-sm text-slate-600">
                                    {{ __('Remember me') }}
                                </span>

                            </label>

                        </div>

                        <!-- LOGIN BUTTON -->
                        <button
                            type="submit"
                            class="w-full py-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold transition duration-200">
                            {{ __('Log in') }}
                        </button>

                    </form>

                </div>


                <!-- SECURITY -->
                <div class="flex items-center justify-center gap-2 py-3 text-xs text-slate-400">

                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>

                    <span>
                        Secure access to your team workspace
                    </span>

                </div>

            </div>

        </div>

    </div>

</x-guest-layout>