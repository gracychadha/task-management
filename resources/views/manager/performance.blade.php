<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-purple-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 00-3-3.87"></path><path d="M16 3.13a4 4 0 010 7.75"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600">Team Analytics</p>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">Department Performance</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $memberStats->count() }} team members tracked</p>
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

            <!-- Summary Stats -->
            @php
                $totalTasks = $memberStats->sum('total_tasks');
                $totalCompleted = $memberStats->sum('completed_tasks');
                $totalOverdue = $memberStats->sum('overdue_tasks');
                $avgRate = $memberStats->count() > 0 ? round($memberStats->avg('completion_rate'), 1) : 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 p-6 text-white shadow-lg shadow-purple-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-purple-100">Total Tasks</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $totalTasks }}</div>
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
                            <div class="text-sm font-medium text-emerald-100">Completed</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $totalCompleted }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 text-white shadow-lg shadow-rose-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-rose-100">Overdue</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $totalOverdue }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-6 text-white shadow-lg shadow-sky-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-sky-100">Avg. Completion</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $avgRate }}%</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"></path><path d="M12 20V4"></path><path d="M6 20v-6"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Performance Cards -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-purple-500 to-violet-600"></div>
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 00-3-3.87"></path><path d="M16 3.13a4 4 0 010 7.75"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Member Performance</h3>
                            <p class="text-xs text-gray-500">Individual task metrics for each team member</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($memberStats as $member)
                            <div class="p-5 rounded-xl border border-gray-100 hover:border-purple-200 hover:shadow-md transition-all">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                            {{ collect(explode(' ', $member['name']))->map(fn($w) => mb_substr($w, 0, 1))->implode('') }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $member['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $member['department'] ?? '' }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $member['completion_rate'] >= 75 ? 'bg-emerald-100 text-emerald-800' : ($member['completion_rate'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $member['completion_rate'] }}%
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                                    <div class="text-center p-2 rounded-lg bg-gray-50">
                                        <p class="text-lg font-bold text-gray-900">{{ $member['total_tasks'] }}</p>
                                        <p class="text-xs text-gray-500">Total</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-emerald-50">
                                        <p class="text-lg font-bold text-emerald-600">{{ $member['completed_tasks'] }}</p>
                                        <p class="text-xs text-gray-500">Done</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-sky-50">
                                        <p class="text-lg font-bold text-sky-600">{{ $member['in_progress_tasks'] }}</p>
                                        <p class="text-xs text-gray-500">Active</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-amber-50">
                                        <p class="text-lg font-bold text-amber-600">{{ $member['under_review_tasks'] }}</p>
                                        <p class="text-xs text-gray-500">Review</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-orange-50">
                                        <p class="text-lg font-bold {{ $member['changes_requested_tasks'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $member['changes_requested_tasks'] }}</p>
                                        <p class="text-xs text-gray-500">Changes</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-red-50">
                                        <p class="text-lg font-bold {{ $member['overdue_tasks'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $member['overdue_tasks'] }}</p>
                                        <p class="text-xs text-gray-500">Overdue</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-gray-50">
                                        <p class="text-lg font-bold text-gray-600">{{ $member['review_cycles'] }}</p>
                                        <p class="text-xs text-gray-500">Reviews</p>
                                    </div>
                                    <div class="text-center p-2 rounded-lg bg-gray-50">
                                        <p class="text-lg font-bold {{ $member['resubmissions'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $member['resubmissions'] }}</p>
                                        <p class="text-xs text-gray-500">Resubmit</p>
                                    </div>
                                </div>

                                <!-- Progress bar -->
                                <div class="mt-3">
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-purple-500 to-violet-600 h-2 rounded-full transition-all duration-500" style="width: {{ $member['completion_rate'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                                </div>
                                <p class="text-sm text-gray-500">No team members found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Monthly Trend -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-sky-500 to-blue-600"></div>
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"></path><path d="M12 20V4"></path><path d="M6 20v-6"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Monthly Trend</h3>
                            <p class="text-xs text-gray-500">Task completion over the last 6 months</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach($tasksByMonth as $month => $data)
                            @php
                                $pct = $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-gray-600 font-medium">{{ $month }}</span>
                                    <span class="font-bold text-gray-900">{{ $data['completed'] }}/{{ $data['total'] }} <span class="text-gray-400 font-normal">({{ $pct }}%)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>