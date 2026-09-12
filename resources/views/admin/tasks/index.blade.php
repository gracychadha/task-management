<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 7v10M12 7v10M16 7v10"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tasks</h1>
                    <p class="text-sm text-gray-500">Manage all tasks across every department</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden lg:block">
                    <p class="text-sm font-semibold text-gray-900" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleDateString('en-US', { weekday:'long', month:'short', day:'numeric', year:'numeric' }) + ' · ' + new Date().toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'}), 1000)" x-text="time"></p>
                </div>
                <div class="flex rounded-xl border border-gray-200 bg-gray-50 p-1">
                    <a href="{{ route('admin.tasks.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $view === 'kanban' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/25' : 'text-gray-600 hover:text-gray-900' }}">Board</a>
                    <a href="{{ route('admin.tasks.index', array_merge(request()->except('view'), ['view' => 'list'])) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $view === 'list' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/25' : 'text-gray-600 hover:text-gray-900' }}">List</a>
                </div>
                <a href="{{ route('admin.tasks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition">
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
                <div class="flex flex-wrap gap-4 items-end justify-between">
                    <form method="GET" class="flex flex-wrap gap-3 items-end flex-1">
                        <div>
                            <x-input-label value="Search" class="text-xs font-medium text-gray-500 uppercase" />
                            <div class="mt-1 relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <x-text-input name="search" value="{{ request('search') }}" placeholder="Search tasks..." class="pl-9 text-sm rounded-xl" />
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Status" class="text-xs font-medium text-gray-500 uppercase" />
                            <select name="status" class="mt-1 border-gray-300 rounded-xl shadow-sm text-sm">
                                <option value="">All Status</option>
                                @foreach(\App\Enums\TaskStatus::cases() as $s)
                                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Priority" class="text-xs font-medium text-gray-500 uppercase" />
                            <select name="priority" class="mt-1 border-gray-300 rounded-xl shadow-sm text-sm">
                                <option value="">All Priority</option>
                                @foreach(\App\Enums\TaskPriority::cases() as $p)
                                    <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Department" class="text-xs font-medium text-gray-500 uppercase" />
                            <select name="department_id" class="mt-1 border-gray-300 rounded-xl shadow-sm text-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Assignee" class="text-xs font-medium text-gray-500 uppercase" />
                            <select name="assigned_to" class="mt-1 border-gray-300 rounded-xl shadow-sm text-sm">
                                <option value="">All Assignees</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            {{-- Kanban View --}}
            @if($view === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4">
                @php
                    $statuses = [
                        'new' => ['label' => 'New', 'color' => 'gray'],
                        'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
                        'under_review' => ['label' => 'Under Review', 'color' => 'yellow'],
                        'changes_requested' => ['label' => 'Changes Requested', 'color' => 'red'],
                        'on_hold' => ['label' => 'On Hold', 'color' => 'purple'],
                        'completed' => ['label' => 'Completed', 'color' => 'green'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'zinc'],
                    ];
                @endphp
                @foreach($statuses as $statusKey => $statusInfo)
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 bg-white border-b border-gray-200">
                            <div class="w-3 h-3 rounded-full bg-{{ $statusInfo['color'] }}-500"></div>
                            <h3 class="font-semibold text-gray-700 text-sm">{{ $statusInfo['label'] }}</h3>
                            <span class="ml-auto text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $kanbanTasks->get($statusKey, collect())->count() }}</span>
                        </div>
                        <div class="p-3 space-y-3 min-h-[60px]" id="column-{{ $statusKey }}">
                            @foreach($kanbanTasks->get($statusKey, collect()) as $task)
                                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200 hover:shadow-md hover:border-indigo-200 transition-all cursor-pointer"
                                     draggable="true"
                                     x-data
                                     x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $task->id }}')"
                                     x-on:dragover.prevent
                                     x-on:drop.prevent="moveTask($event, '{{ $task->id }}', '{{ $statusKey }}')">
                                    <a href="{{ route('admin.tasks.show', $task) }}">
                                        <div class="font-semibold text-sm text-gray-900 mb-1 flex items-start justify-between gap-2">
                                            <span class="break-words">{{ $task->title }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold
                                                @if($task->priority === 'urgent') bg-red-100 text-red-700
                                                @elseif($task->priority === 'high') bg-orange-100 text-orange-700
                                                @elseif($task->priority === 'medium') bg-blue-100 text-blue-700
                                                @else bg-gray-100 text-gray-600 @endif">
                                                {{ $task->getPriorityLabel() }}
                                            </span>
                                            @if($task->due_date)
                                                <span class="text-[11px] font-medium {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">{{ $task->due_date->format('M d') }}</span>
                                            @endif
                                        </div>
                                        @if($task->assignees->count())
                                            <div class="flex -space-x-2">
                                                @foreach($task->assignees->take(3) as $assignee)
                                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 border-2 border-white flex items-center justify-center text-[8px] text-white font-semibold" title="{{ $assignee->name }}">{{ $assignee->initials() }}</div>
                                                @endforeach
                                                @if($task->assignees->count() > 3)
                                                    <div class="w-6 h-6 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[8px] text-gray-600 font-semibold">+{{ $task->assignees->count() - 3 }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @else
                {{-- List View --}}
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
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900 cursor-pointer" onclick="location.href='{{ route('admin.tasks.show', $task) }}'">
                                        <span class="text-xs font-medium text-indigo-600">{{ $task->code() }}</span><br>
                                        {{ $task->title }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
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
                                        <div x-data="{ open: false }" class="relative inline-block">
                                            <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                                Manage
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-xl shadow-xl z-50 text-left">
                                                <a href="{{ route('admin.tasks.show', $task) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View
                                                </a>
                                                <a href="{{ route('admin.tasks.edit', $task) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Edit
                                                </a>
                                                <hr class="border-gray-100">
                                                <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
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
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function moveTask(event, taskId, currentStatus) {
            const newStatus = event.currentTarget.id.replace('column-', '');
            if (newStatus === currentStatus) return;

            fetch(`/admin/api/tasks/${taskId}/move`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            }).then(() => location.reload());
        }
    </script>
    @endpush
</x-app-layout>