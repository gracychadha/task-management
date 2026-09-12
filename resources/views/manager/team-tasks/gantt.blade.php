<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gantt Chart</h2>
            <div class="flex gap-2">
                <a href="{{ route('manager.team-tasks') }}" class="text-sm text-gray-600 hover:text-gray-900">List</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('manager.team-tasks.board') }}" class="text-sm text-gray-600 hover:text-gray-900">Board</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('manager.team-tasks.calendar') }}" class="text-sm text-gray-600 hover:text-gray-900">Calendar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                @php
                    $startDate = $tasks->min('start_date') ?? now()->subMonth();
                    $endDate = $tasks->max('due_date') ?? now()->addMonth();
                    $totalDays = max(1, $startDate->diffInDays($endDate) + 1);
                    $today = now();
                @endphp

                <div class="min-w-[800px]">
                    <div class="flex items-center mb-2">
                        <div class="w-48 shrink-0 text-xs font-medium text-gray-500">Task</div>
                        <div class="flex-1 relative h-8 bg-gray-100 rounded">
                            @for($i = 0; $i < $totalDays; $i += max(1, floor($totalDays / 14)))
                                @php $day = $startDate->copy()->addDays($i); @endphp
                                <div class="absolute text-[10px] text-gray-500" style="left: {{ ($i / $totalDays) * 100 }}%">{{ $day->format('M d') }}</div>
                            @endfor
                        </div>
                    </div>

                    <div class="relative">
                        @foreach($tasks as $task)
                            @php
                                $taskStart = $task->start_date ? max(0, $startDate->diffInDays($task->start_date)) : 0;
                                $taskDuration = ($task->start_date && $task->due_date) ? max(1, $task->start_date->diffInDays($task->due_date) + 1) : 1;
                                $leftPct = ($taskStart / $totalDays) * 100;
                                $widthPct = max(1, ($taskDuration / $totalDays) * 100);
                            @endphp
                            <div class="flex items-center mb-1">
                                <div class="w-48 shrink-0 text-xs text-gray-700 truncate pr-2">{{ $task->title }}</div>
                                <div class="flex-1 relative h-6">
                                    <div class="absolute h-4 top-1 rounded cursor-pointer
                                        @switch($task->status)
                                            @case('new') bg-gray-400 @break
                                            @case('in_progress') bg-blue-400 @break
                                            @case('under_review') bg-yellow-400 @break
                                            @case('changes_requested') bg-red-400 @break
                                            @case('on_hold') bg-purple-400 @break
                                            @case('completed') bg-green-400 @break
                                            @case('cancelled') bg-zinc-400 @break
                                            @default bg-gray-400
                                        @endswitch"
                                        style="left: {{ $leftPct }}%; width: {{ $widthPct }}%;"
                                        title="{{ $task->title }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($tasks->isEmpty())
                        <p class="text-center text-gray-500 py-8">No tasks with start dates found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
