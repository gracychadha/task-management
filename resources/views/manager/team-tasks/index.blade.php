<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 7v10M12 7v10M16 7v10"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Team Tasks</h1>
                    <p class="text-sm text-gray-500">Tasks assigned to your team</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="hidden lg:flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-xl p-1">
                    <a href="{{ route('manager.team-tasks.board') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="11" rx="1"/></svg>
                        Board
                    </a>
                    <a href="{{ route('manager.team-tasks.calendar') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Calendar
                    </a>
                    <a href="{{ route('manager.team-tasks.gantt') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14h6M7 10h10M7 6h4"/></svg>
                        Gantt
                    </a>
                </div>
                <div class="text-right hidden lg:block">
                    <p class="text-sm font-semibold text-gray-900" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleDateString('en-US', { weekday:'long', month:'short', day:'numeric', year:'numeric' }) + ' · ' + new Date().toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'}), 1000)" x-text="time"></p>
                </div>
                <a href="{{ route('manager.team-tasks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Task
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 p-6 text-white shadow-lg shadow-indigo-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-indigo-100">Total Tasks</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 7v10M12 7v10M16 7v10"/></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-6 text-white shadow-lg shadow-sky-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-sky-100">In Progress</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['in_progress'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-9-9"/><path d="M12 3v9l6 3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-lg shadow-emerald-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-emerald-100">Completed</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['completed'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 text-white shadow-lg shadow-rose-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-rose-100">Overdue</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['overdue'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label value="Search" class="text-xs font-medium text-gray-500 uppercase" />
                        <div class="mt-1 relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <x-text-input name="search" value="{{ request('search') }}" class="pl-9 block w-full rounded-xl" placeholder="Search tasks..." />
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Status" class="text-xs font-medium text-gray-500 uppercase" />
                        <select name="status" class="mt-1 border-gray-300 rounded-xl shadow-sm">
                            <option value="">All</option>
                            @foreach(\App\Enums\TaskStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Priority" class="text-xs font-medium text-gray-500 uppercase" />
                        <select name="priority" class="mt-1 border-gray-300 rounded-xl shadow-sm">
                            <option value="">All</option>
                            @foreach(\App\Enums\TaskPriority::cases() as $p)
                                <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Assignee" class="text-xs font-medium text-gray-500 uppercase" />
                        <select name="assigned_to" class="mt-1 border-gray-300 rounded-xl shadow-sm">
                            <option value="">All</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ request('assigned_to') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filter
                    </button>
                </form>
            </div>

            {{-- Task List --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Assignees</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($tasks as $task)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900 cursor-pointer" onclick="location.href='{{ route('manager.team-tasks.show', $task) }}'">
                                        <span class="text-xs font-medium text-indigo-600">{{ $task->code() }}</span><br>
                                        <span class="text-gray-700">{{ $task->title }}</span>
                                        @if($task->isOverdue())
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Overdue</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
                                        @if($task->isInReviewCycle())
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $task->getReviewStatusColor() }}">
                                                    {{ $task->getReviewStatusLabel() }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            @if($task->priority === 'urgent') bg-red-100 text-red-800
                                            @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                            @elseif($task->priority === 'medium') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $task->getPriorityLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @forelse($task->assignees as $assignee)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-800 text-xs font-medium mr-1 mb-1">
                                                <span class="w-4 h-4 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-[7px] text-white font-bold">{{ $assignee->initials() }}</span>
                                                {{ $assignee->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400">—</span>
                                        @endforelse
                                    </td>
                                    <td class="px-5 py-4 text-center text-sm font-medium {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-600' }}">{{ $task->due_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="{{ route('manager.team-tasks.show', $task) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                            View
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-sm text-gray-500 text-center">
                                        <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                            <svg class="w-7 h-7 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 7v10M12 7v10M16 7v10"/></svg>
                                        </div>
                                        No tasks found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tasks->hasPages())
                    <div class="p-4 border-t border-gray-100">{{ $tasks->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>