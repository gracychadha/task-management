<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Task Calendar</h2>
            <div class="flex gap-2">
                <a href="{{ route('manager.team-tasks') }}" class="text-sm text-gray-600 hover:text-gray-900">List</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('manager.team-tasks.board') }}" class="text-sm text-gray-600 hover:text-gray-900">Board</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('manager.team-tasks.gantt') }}" class="text-sm text-gray-600 hover:text-gray-900">Gantt</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-7 gap-px bg-gray-200 mb-4">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="bg-gray-50 p-2 text-center text-xs font-medium text-gray-500">{{ $day }}</div>
                    @endforeach
                </div>

                @php
                    $start = now()->startOfMonth()->startOfWeek();
                    $end = now()->endOfMonth()->endOfWeek();
                    $current = $start->copy();
                @endphp

                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    @while($current <= $end)
                        @php
                            $isToday = $current->isToday();
                            $isCurrentMonth = $current->isCurrentMonth();
                            $dayTasks = $tasks->filter(fn($t) => $t->due_date && $t->due_date->isSameDay($current));
                        @endphp
                        <div class="bg-white min-h-[100px] p-2 {{ !$isCurrentMonth ? 'opacity-40' : '' }}">
                            <div class="text-xs font-medium {{ $isToday ? 'bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center' : 'text-gray-700' }}">{{ $current->format('d') }}</div>
                            <div class="mt-1 space-y-1">
                                @foreach($dayTasks->take(3) as $task)
                                    <a href="{{ route('manager.team-tasks.show', $task) }}" class="block text-[10px] px-1 py-0.5 rounded truncate
                                        @switch($task->status)
                                            @case('new') bg-gray-100 text-gray-800 @break
                                            @case('in_progress') bg-blue-100 text-blue-700 @break
                                            @case('under_review') bg-yellow-100 text-yellow-700 @break
                                            @case('changes_requested') bg-red-100 text-red-700 @break
                                            @case('on_hold') bg-purple-100 text-purple-700 @break
                                            @case('completed') bg-green-100 text-green-700 @break
                                            @case('cancelled') bg-zinc-100 text-zinc-700 @break
                                            @default bg-gray-100 text-gray-700
                                        @endswitch">
                                        {{ $task->title }}
                                    </a>
                                @endforeach
                                @if($dayTasks->count() > 3)
                                    <div class="text-[10px] text-gray-500">+{{ $dayTasks->count() - 3 }} more</div>
                                @endif
                            </div>
                        </div>
                        @php $current->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
