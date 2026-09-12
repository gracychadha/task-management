<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-purple-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600">Audit Trail</p>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">Activity Logs</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $stats['total'] }} total activities recorded</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200">
                <div class="w-9 h-9 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 leading-tight">{{ now()->format('l, F j, Y') }}</p>
                    <p class="text-xs text-gray-500 tabular-nums">{{ now()->format('g:i:s A') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 p-6 text-white shadow-lg shadow-purple-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-purple-100">Total Activities</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['total'] }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-6 text-white shadow-lg shadow-sky-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-sky-100">Today</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['today'] }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-lg shadow-emerald-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-emerald-100">Active Users (7d)</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['active_users'] }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 00-3-3.87"></path><path d="M16 3.13a4 4 0 010 7.75"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Search</label>
                        <div class="relative mt-1.5">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                            <input name="search" value="{{ request('search') }}" placeholder="Search activities..." class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm text-sm" />
                        </div>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">User</label>
                        <select name="user" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm text-sm py-2.5">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</label>
                        <div class="relative mt-1.5">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <input name="date" type="date" value="{{ request('date') }}" class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm text-sm" />
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-purple-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        Filter
                    </button>
                </form>
            </div>

            <!-- Activity List -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-purple-500 to-violet-600"></div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($activities as $activity)
                            @php
                                $parts = explode(' ', $activity->description ?? '');
                                $verb = $parts[0] ?? '';
                                $colorClass = match(true) {
                                    str_starts_with($verb, 'created') => 'bg-emerald-100 text-emerald-800',
                                    str_starts_with($verb, 'updated') => 'bg-sky-100 text-sky-800',
                                    str_starts_with($verb, 'deleted') => 'bg-red-100 text-red-800',
                                    str_starts_with($verb, 'moved') => 'bg-amber-100 text-amber-800',
                                    str_starts_with($verb, 'activated') => 'bg-emerald-100 text-emerald-800',
                                    str_starts_with($verb, 'deactivated') => 'bg-orange-100 text-orange-800',
                                    str_starts_with($verb, 'submitted') => 'bg-blue-100 text-blue-800',
                                    str_starts_with($verb, 'approved') => 'bg-green-100 text-green-800',
                                    str_starts_with($verb, 'requested') => 'bg-amber-100 text-amber-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-purple-200 hover:bg-purple-50/50 transition">
                                <div class="flex items-center gap-4 min-w-0">
                                    @if($activity->causer)
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-white text-xs font-bold shadow-sm flex-shrink-0">
                                            {{ $activity->causer->initials() }}
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"></path></svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-semibold text-gray-900">{{ $activity->causer?->name ?? 'System' }}</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $colorClass }}">{{ ucfirst($verb) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-0.5">{{ $activity->description }}</p>
                                        @if($activity->causer?->email)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $activity->causer->email }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-gray-400 tabular-nums">{{ $activity->created_at->format('M d, g:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </div>
                                <p class="text-sm text-gray-500">No activity logs found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="px-6 pb-6">{{ $activities->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>