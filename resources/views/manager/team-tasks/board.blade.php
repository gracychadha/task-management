<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Task Board</h2>
            <div class="flex gap-2">
                <a href="{{ route('manager.team-tasks') }}" class="text-sm text-indigo-600 hover:text-indigo-500">List</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('manager.team-tasks.calendar') }}" class="text-sm text-gray-600 hover:text-gray-900">Calendar</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('manager.team-tasks.gantt') }}" class="text-sm text-gray-600 hover:text-gray-900">Gantt</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5 gap-4">
                @php
                    $statuses = [
                        'new' => ['label' => 'New', 'color' => 'gray'],
                        'in_progress' => ['label' => 'In Progress', 'color' => 'blue'],
                        'under_review' => ['label' => 'Under Review', 'color' => 'yellow'],
                        'changes_requested' => ['label' => 'Changes Requested', 'color' => 'red'],
                        'on_hold' => ['label' => 'On Hold', 'color' => 'purple'],
                    ];
                @endphp
                @foreach($statuses as $statusKey => $statusInfo)
                    <div class="bg-gray-50 rounded-lg p-4 min-h-[400px]" id="column-{{ $statusKey }}">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-3 h-3 rounded-full bg-{{ $statusInfo['color'] }}-500"></div>
                            <h3 class="font-medium text-gray-700 text-sm">{{ $statusInfo['label'] }}</h3>
                            <span class="text-xs text-gray-500 bg-white px-2 py-0.5 rounded-full">{{ $tasks->get($statusKey, collect())->count() }}</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($tasks->get($statusKey, collect()) as $task)
                                <div data-task-id="{{ $task->id }}" draggable="true" class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                                    <a href="{{ route('manager.team-tasks.show', $task) }}">
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
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @foreach($task->assignees->take(3) as $assignee)
                                                    <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-[8px] text-indigo-600 font-medium" title="{{ $assignee->name }}">{{ $assignee->initials() }}</div>
                                                @endforeach
                                                @if($task->assignees->count() > 3)
                                                    <span class="text-[10px] text-gray-500">+{{ $task->assignees->count() - 3 }}</span>
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
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('[draggable="true"]').forEach(el => {
            el.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', el.dataset.taskId);
                el.style.opacity = '0.5';
            });
            el.addEventListener('dragend', e => {
                el.style.opacity = '1';
            });
        });

        document.querySelectorAll('[id^="column-"]').forEach(col => {
            col.addEventListener('dragover', e => e.preventDefault());
            col.addEventListener('drop', e => {
                e.preventDefault();
                const taskId = e.dataTransfer.getData('text/plain');
                const status = col.id.replace('column-', '');
                if (!taskId) return;
                fetch(`/manager/api/tasks/${taskId}/move`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                }).then(() => location.reload());
            });
        });
    </script>
    @endpush
</x-app-layout>
