<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">My Dashboard</h2>
                <p class="text-sm text-gray-500 mt-0.5">Welcome back — here's your personal overview.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">My Tasks</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ $totalAssigned }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">In Progress</div>
                        <div class="mt-1 text-3xl font-bold text-blue-600">{{ $inProgressTasks }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Completed</div>
                        <div class="mt-1 text-3xl font-bold text-emerald-600">{{ $completedTasks }}</div>
                        <div class="mt-1 text-xs text-gray-500">{{ $completionRate }}% rate</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Overdue</div>
                        <div class="mt-1 text-3xl font-bold text-red-600">{{ $overdueTasks }}</div>
                    </div>
                </div>
            </div>

            <!-- My Active Tasks & Upcoming Deadlines -->
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

            <!-- Task Status & Recently Completed -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"></path><path d="M12 20V4"></path><path d="M6 20v-6"></path></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900">Task Status</h3>
                    </div>
                    <div class="space-y-4">
                        @php
                            $labels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];
                            $colors = ['todo' => 'bg-gray-400', 'in_progress' => 'bg-blue-500', 'review' => 'bg-yellow-500', 'done' => 'bg-emerald-500'];
                        @endphp
                        @foreach($tasksByStatus as $status => $count)
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-gray-600 font-medium">{{ $labels[$status] ?? $status }}</span>
                                    <span class="text-gray-500 font-medium">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5">
                                    <div class="{{ $colors[$status] ?? 'bg-gray-400' }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $totalAssigned > 0 ? round(($count / $totalAssigned) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900">Recently Completed</h3>
                    </div>
                    <div class="space-y-1">
                        @forelse($recentCompleted as $task)
                            <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
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
    </div>
</x-app-layout>
