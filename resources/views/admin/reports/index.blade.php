<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <x-input-label value="From" />
                        <x-text-input name="date_from" type="date" value="{{ $dateFrom }}" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="To" />
                        <x-text-input name="date_to" type="date" value="{{ $dateTo }}" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Department" />
                        <select name="department_id" class="border-gray-300 rounded-md shadow-sm mt-1">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Status" />
                        <select name="status" class="border-gray-300 rounded-md shadow-sm mt-1">
                            <option value="">All Status</option>
                            @foreach(\App\Enums\TaskStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button type="submit">Filter</x-primary-button>
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ $totalTasks }}</div>
                    <div class="text-sm text-gray-500">Total Tasks</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $completedTasks }}</div>
                    <div class="text-sm text-gray-500">Completed</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $inProgressTasks }}</div>
                    <div class="text-sm text-gray-500">In Progress</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-red-600">{{ $overdueTasks }}</div>
                    <div class="text-sm text-gray-500">Overdue</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $completionRate }}%</div>
                    <div class="text-sm text-gray-500">Completion</div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-3">
                <a href="{{ route('admin.reports.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                    Export Excel
                </a>
                <a href="{{ route('admin.reports.export-pdf', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                    Export PDF
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Status Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tasks by Status</h3>
                    <div class="space-y-3">
                        @foreach($statusBreakdown['labels'] as $i => $label)
                            @php $count = $statusBreakdown['data'][$i]; @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">{{ $label }}</span>
                                    <span class="font-medium">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? round(($count / $totalTasks) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Department Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tasks by Department</h3>
                    <div class="space-y-3">
                        @forelse($deptBreakdown as $deptData)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">{{ $deptData['name'] }}</span>
                                    <span class="font-medium">{{ $deptData['total'] }} total, {{ $deptData['completed'] }} done</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $deptData['total'] > 0 ? round(($deptData['completed'] / $deptData['total']) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No department data</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Task Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Task Details</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Priority</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Assignees</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Department</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($tasks->take(50) as $task)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $task->title }}</td>
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
                                    <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $task->assignees->pluck('name')->implode(', ') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $task->assignees->pluck('department.name')->unique()->implode(', ') ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $task->created_at?->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-sm text-gray-500 text-center">No tasks found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
