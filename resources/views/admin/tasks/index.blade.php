<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tasks</h2>
            <a href="{{ route('admin.tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Create Task</a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ view: '{{ $view }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters & View Toggle -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <form method="GET" class="flex flex-wrap gap-3 items-end flex-1">
                        <x-text-input name="search" value="{{ request('search') }}" placeholder="Search tasks..." class="text-sm" />
                        <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All Status</option>
                            @foreach(\App\Enums\TaskStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                        <select name="priority" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All Priority</option>
                            @foreach(\App\Enums\TaskPriority::cases() as $p)
                                <option value="{{ $p->value }}" {{ request('priority') === $p->value ? 'selected' : '' }}>{{ $p->label() }}</option>
                            @endforeach
                        </select>
                        <select name="department_id" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <select name="assigned_to" class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">All Assignees</option>
                            @foreach($departments->flatMap(fn($d) => $d->users) as $user)
                                <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <x-primary-button type="submit" class="text-xs">Filter</x-primary-button>
                    </form>
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                        <button @click="view = 'kanban'" :class="view === 'kanban' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700'" class="px-3 py-1.5 text-xs font-medium">Board</button>
                        <button @click="view = 'list'" :class="view === 'list' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700'" class="px-3 py-1.5 text-xs font-medium">List</button>
                    </div>
                </div>
            </div>

            <!-- Kanban View -->
            <div x-show="view === 'kanban'" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $statuses = [
                        'todo' => ['label' => 'To Do', 'color' => 'gray'],
                        'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
                        'review' => ['label' => 'Review', 'color' => 'yellow'],
                        'done' => ['label' => 'Done', 'color' => 'green'],
                    ];
                @endphp
                @foreach($statuses as $statusKey => $statusInfo)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-3 h-3 rounded-full bg-{{ $statusInfo['color'] }}-500"></div>
                            <h3 class="font-medium text-gray-700 text-sm">{{ $statusInfo['label'] }}</h3>
                            <span class="text-xs text-gray-500 bg-white px-2 py-0.5 rounded-full">{{ $kanbanTasks->get($statusKey, collect())->count() }}</span>
                        </div>
                        <div class="space-y-3" id="column-{{ $statusKey }}">
                            @foreach($kanbanTasks->get($statusKey, collect()) as $task)
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow cursor-pointer"
                                     draggable="true"
                                     x-data
                                     x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $task->id }}')"
                                     x-on:dragover.prevent
                                     x-on:drop.prevent="moveTask($event, '{{ $task->id }}', '{{ $statusKey }}')">
                                    <a href="{{ route('admin.tasks.show', $task) }}">
                                        <div class="font-medium text-sm text-gray-900 mb-1">{{ $task->title }}</div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium
                                                @if($task->priority === 'urgent') bg-red-100 text-red-700
                                                @elseif($task->priority === 'high') bg-orange-100 text-orange-700
                                                @elseif($task->priority === 'medium') bg-blue-100 text-blue-700
                                                @else bg-gray-100 text-gray-600 @endif">
                                                {{ $task->getPriorityLabel() }}
                                            </span>
                                            @if($task->due_date)
                                                <span class="text-[10px] {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">{{ $task->due_date->format('M d') }}</span>
                                            @endif
                                        </div>
                                        @if($task->assignees->count())
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($task->assignees as $assignee)
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-[8px] text-indigo-600 font-medium">{{ $assignee->initials() }}</div>
                                                        <span class="text-[10px] text-gray-500">{{ $assignee->name }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- List View -->
            <div x-show="view === 'list'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Priority</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Assignees</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($tasks as $task)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 cursor-pointer" onclick="location.href='{{ route('admin.tasks.show', $task) }}'">{{ $task->title }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($task->status === 'done') bg-green-100 text-green-800
                                            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                            @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $task->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($task->priority === 'urgent') bg-red-100 text-red-800
                                            @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                            @elseif($task->priority === 'medium') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $task->getPriorityLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500">
                                        @forelse($task->assignees as $assignee)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs mr-1">{{ $assignee->name }}</span>
                                        @empty
                                            -
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">{{ $task->due_date?->format('M d, Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div x-data="{ open: false }" class="relative inline-block">
                                            <button @click="open = !open" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                                Manage
                                                <svg class="ml-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-xl z-50 text-left">
                                                <a href="{{ route('admin.tasks.show', $task) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View
                                                </a>
                                                <a href="{{ route('admin.tasks.edit', $task) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Edit
                                                </a>
                                                <hr class="border-gray-100">
                                                <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-sm text-gray-500 text-center">No tasks found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $tasks->links() }}</div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function moveTask(event, taskId, currentStatus) {
            const statuses = ['todo', 'in_progress', 'review', 'done'];
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
