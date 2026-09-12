<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <!-- Left: Greeting -->
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-lg font-bold shadow-md shadow-indigo-500/20">
                    {{ auth()->user()->initials() }}
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">My Dashboard</p>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">Welcome back, {{ auth()->user()->name }}!</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Have a good day at work.</p>
                </div>
            </div>

            <!-- Right: Date & time + actions -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div>
                        <p id="dashboard-date" class="text-sm font-semibold text-gray-900 leading-tight">{{ now()->format('l, F j, Y') }}</p>
                        <p id="dashboard-time" class="text-xs text-gray-500 tabular-nums">{{ now()->format('g:i:s A') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Profile
                    </a>
                    <a href="{{ route('employee.my-tasks') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        My Tasks
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 p-6 text-white shadow-lg shadow-indigo-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-indigo-100">My Tasks</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $totalAssigned }}</div>
                            <div class="mt-1 text-xs text-indigo-100/80">{{ $newTasks }} new, {{ $inProgressTasks }} in progress</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-6 text-white shadow-lg shadow-sky-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-sky-100">In Progress</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $inProgressTasks }}</div>
                            <div class="mt-1 text-xs text-sky-100/80">{{ $underReviewTasks }} under review</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-lg shadow-emerald-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-emerald-100">Completed</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $completedTasks }}</div>
                            <div class="mt-1 text-xs text-emerald-100/80">{{ $completionRate }}% completion rate</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 text-white shadow-lg shadow-rose-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-rose-100">Overdue</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $overdueTasks }}</div>
                            <div class="mt-1 text-xs text-rose-100/80">requires attention</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"></path><path d="M12 20V4"></path><path d="M6 20v-6"></path></svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">Tasks by Status</h3>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @php
                            $colors = [
                                'new' => 'bg-gray-400', 'in_progress' => 'bg-blue-500', 'under_review' => 'bg-yellow-500',
                                'changes_requested' => 'bg-red-500', 'completed' => 'bg-emerald-500', 'on_hold' => 'bg-purple-500', 'cancelled' => 'bg-zinc-400',
                            ];
                        @endphp
                        @foreach($tasksByStatus as $status => $count)
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-gray-600 font-medium">{{ \App\Enums\TaskStatus::tryFrom($status)?->label() ?? $status }}</span>
                                    <span class="text-gray-500">{{ $count }} <span class="text-gray-400">({{ $totalAssigned > 0 ? round(($count / $totalAssigned) * 100) : 0 }}%)</span></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="{{ $colors[$status] ?? 'bg-gray-400' }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $totalAssigned > 0 ? round(($count / $totalAssigned) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">Tasks by Priority</h3>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @php
                            $priorityColors = ['low' => 'bg-gray-400', 'medium' => 'bg-blue-500', 'high' => 'bg-orange-500', 'urgent' => 'bg-red-500'];
                            $priorityLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
                        @endphp
                        @foreach($priorityLabels as $key => $label)
                            @php $count = $priorityCounts[$key] ?? 0; @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-gray-600 font-medium">{{ $label }}</span>
                                    <span class="text-gray-500">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="{{ $priorityColors[$key] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $totalAssigned > 0 ? round(($count / $totalAssigned) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- My Active Tasks & Deadlines -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">My Active Tasks</h3>
                        </div>
                        <a href="{{ route('employee.my-tasks') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                            View all
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </div>
                    <div class="space-y-1">
                        @forelse($myTasks as $task)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                                <div>
                                    <a href="{{ route('employee.my-tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $task->assignees->pluck('name')->implode(', ') ?: '' }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if($task->priority === 'urgent') bg-red-100 text-red-800
                                        @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                        @elseif($task->priority === 'medium') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $task->getPriorityLabel() }}
                                    </span>
                                    @if($task->due_date)
                                        <span class="text-xs font-medium {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">{{ $task->due_date->format('M d') }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No active tasks. Great job!</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-6">
                    @if($pendingReviewCount > 0)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900">Tasks to Review</h3>
                            </div>
                            <a href="{{ route('employee.review-tasks') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View all</a>
                        </div>
                        <div class="space-y-1">
                            @forelse($reviewTasks as $task)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                    <div class="min-w-0">
                                        <a href="{{ route('employee.my-tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">{{ $task->title }}</a>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned' }}</div>
                                    </div>
                                    @if($task->status === 'under_review')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 shrink-0 ml-3">Awaiting you</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Nothing to review.</p>
                            @endforelse
                        </div>
                    </div>
                    @endif

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-center gap-2 mb-5">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900">Upcoming Deadlines</h3>
                        </div>
                        <div class="space-y-1">
                            @forelse($upcomingTasks as $task)
                                <div class="py-2 border-b border-gray-100 last:border-0">
                                    <a href="{{ route('employee.my-tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a>
                                    <div class="text-xs text-gray-500 mt-0.5">Due {{ $task->due_date->format('M d, Y') }}</div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No upcoming deadlines.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recently Completed -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Recently Completed</h3>
                        <p class="text-xs text-gray-500">Your latest completed tasks</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($recentCompleted as $task)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50 hover:shadow-md transition-shadow">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</div>
                                <div class="text-xs text-gray-500">{{ $task->assignees->pluck('name')->implode(', ') ?: '' }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No completed tasks yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            function updateDashboardClock() {
                const now = new Date();
                const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                const date = now.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                const timeEl = document.getElementById('dashboard-time');
                const dateEl = document.getElementById('dashboard-date');
                if (timeEl) timeEl.textContent = time;
                if (dateEl) dateEl.textContent = date;
            }
            updateDashboardClock();
            setInterval(updateDashboardClock, 1000);
        </script>
    @endpush
</x-app-layout>