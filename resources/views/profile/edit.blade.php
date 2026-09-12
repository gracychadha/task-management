<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <!-- Left: Greeting -->
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-lg font-bold shadow-md shadow-indigo-500/20">
                    {{ auth()->user()->initials() }}
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">My Profile</p>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">Welcome back, {{ auth()->user()->name }}!</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Manage your personal information.</p>
                </div>
            </div>

            <!-- Right: Date & time + actions -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div>
                        <p id="profile-date" class="text-sm font-semibold text-gray-900 leading-tight">{{ now()->format('l, F j, Y') }}</p>
                        <p id="profile-time" class="text-xs text-gray-500 tabular-nums">{{ now()->format('g:i:s A') }}</p>
                    </div>
                </div>
                <a href="{{ match(auth()->user()->role) { 'admin' => route('admin.dashboard'), 'manager' => route('manager.dashboard'), default => route('employee.dashboard') } }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Profile Details -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Profile Details</h3>
                                <p class="text-xs text-gray-500">Update your personal information</p>
                            </div>
                        </div>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Change Password</h3>
                                <p class="text-xs text-gray-500">Keep your account secure</p>
                            </div>
                        </div>
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-rose-500 to-red-600"></div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Delete Account</h3>
                                <p class="text-xs text-gray-500">Permanently remove your account</p>
                            </div>
                        </div>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateProfileClock() {
                const now = new Date();
                const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                const date = now.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                const timeEl = document.getElementById('profile-time');
                const dateEl = document.getElementById('profile-date');
                if (timeEl) timeEl.textContent = time;
                if (dateEl) dateEl.textContent = date;
            }
            updateProfileClock();
            setInterval(updateProfileClock, 1000);
        </script>
    @endpush
</x-app-layout>