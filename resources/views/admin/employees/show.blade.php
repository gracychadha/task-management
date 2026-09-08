<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $employee->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Edit</a>
                <form method="POST" action="{{ route('admin.employees.toggle-active', $employee) }}">
                    @csrf @method('PATCH')
                    <x-secondary-button type="submit">{{ $employee->is_active ? 'Deactivate' : 'Activate' }}</x-secondary-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full bg-{{ $employee->getRoleBadgeColor() }}-100 flex items-center justify-center text-{{ $employee->getRoleBadgeColor() }}-600 text-2xl font-bold">
                        {{ $employee->initials() }}
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $employee->name }}</h3>
                        <p class="text-gray-500">{{ $employee->email }}</p>
                        <div class="flex gap-2 mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $employee->getRoleBadgeColor() }}-100 text-{{ $employee->getRoleBadgeColor() }}-800">{{ ucfirst($employee->role) }}</span>
                            @if($employee->department)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $employee->department->name }}</span>
                            @endif
                            @if($employee->position)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $employee->position }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</div>
                    <div class="text-sm text-gray-500">Total Tasks</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed_tasks'] }}</div>
                    <div class="text-sm text-gray-500">Completed</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['in_progress_tasks'] }}</div>
                    <div class="text-sm text-gray-500">In Progress</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-red-600">{{ $stats['overdue_tasks'] }}</div>
                    <div class="text-sm text-gray-500">Overdue</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm text-gray-500">Completion Rate: <span class="font-bold text-gray-900">{{ $completionRate }}%</span></div>
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Tasks</h3>
                <div class="space-y-3">
                    @forelse($employee->assignedTasks->take(10) as $task)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <a href="{{ route('admin.tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a>
                                <div class="text-xs text-gray-500">{{ $task->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->status === 'done') bg-green-100 text-green-800
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $task->getStatusLabel() }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No tasks assigned yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
