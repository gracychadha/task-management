<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Review Queue</p>
                    <h1 class="text-2xl font-bold text-gray-900">Tasks Under My Review</h1>
                    <p class="text-sm text-gray-500">Submissions assigned to you as the reviewer</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200">
                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 leading-tight">{{ now()->format('l, F j, Y') }}</p>
                    <p class="text-xs text-gray-500 tabular-nums">{{ now()->format('g:i:s A') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-lg shadow-emerald-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-emerald-100">Total Relevant</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['total'] }}</div>
                            <div class="mt-1 text-xs text-emerald-100/80">all my review tasks</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-6 text-white shadow-lg shadow-amber-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-amber-100">Awaiting Review</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['awaiting_review'] }}</div>
                            <div class="mt-1 text-xs text-amber-100/80">need your decision</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 text-white shadow-lg shadow-rose-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-rose-100">Changes Requested</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['changes_requested'] }}</div>
                            <div class="mt-1 text-xs text-rose-100/80">waiting on resubmission</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6"></path><path d="M3.51 15a9 9 0 102.13-9.36L1 10"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-6 text-white shadow-lg shadow-sky-500/20">
                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-sky-100">Completed</div>
                            <div class="mt-2 text-3xl font-extrabold">{{ $stats['completed'] }}</div>
                            <div class="mt-1 text-xs text-sky-100/80">approved & delivered</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" class="flex flex-wrap items-end gap-4">
                        <div>
                            <x-input-label value="Status" />
                            <select name="status" class="mt-1 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm block w-full">
                                <option value="">All Statuses</option>
                                @foreach(\App\Enums\TaskStatus::cases() as $s)
                                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-sm font-medium text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            Filter
                        </button>
                        @if(request('status'))
                            <a href="{{ route('employee.review-tasks') }}" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-xl border border-gray-300 hover:border-gray-400 transition">Reset</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">Task</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">Priority</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">Assignee</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">Submitted</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">Due Date</th>
                                <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wide">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($tasks as $task)
                                <tr class="hover:bg-emerald-50/40 cursor-pointer transition" onclick="location.href='{{ route('employee.my-tasks.show', $task) }}'">
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $task->code() }}</div>
                                        <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($task->title, 40) }}</div>
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
                                    <td class="px-5 py-4 text-center">
                                        @if($task->assignees->count())
                                            <div class="flex items-center justify-center -space-x-2">
                                                @foreach($task->assignees->take(3) as $assignee)
                                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">{{ $assignee->initials() }}</div>
                                                @endforeach
                                                @if($task->assignees->count() > 3)
                                                    <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-600 text-[10px] font-bold flex items-center justify-center ring-2 ring-white">+{{ $task->assignees->count() - 3 }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center text-sm text-gray-500">{{ $task->submitted_at?->format('M d, Y H:i') ?? '-' }}</td>
                                    <td class="px-5 py-4 text-center">
                                        @if($task->due_date)
                                            <span class="text-sm font-medium {{ $task->isOverdue() && $task->status !== 'completed' ? 'text-red-600' : 'text-gray-600' }}">{{ $task->due_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-sm text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($task->status === 'under_review')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Awaiting your review
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center">
                                        <div class="inline-flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg>
                                            <div class="text-sm font-medium text-gray-500">No tasks assigned to you for review.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tasks->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">{{ $tasks->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>