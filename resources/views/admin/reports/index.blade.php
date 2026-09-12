<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 118 2.83"></path><path d="M22 12A10 10 0 0012 2v10z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Analytics</p>
                    <h2 class="text-2xl font-bold text-gray-900 leading-tight">Reports & Analytics</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Task performance metrics and insights</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200">
                <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
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

            <!-- Tabs -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-2">
                <div class="flex flex-wrap gap-1.5">
                    @foreach([
                        'overview' => 'Overview',
                        'employee' => 'Employee Performance',
                        'department' => 'Department Performance',
                        'review' => 'Review Report',
                        'overdue' => 'Overdue',
                        'updates' => 'Updates & Revisions',
                        'turnaround' => 'Turnaround Time',
                    ] as $tabType => $tabLabel)
                        <a href="{{ route('admin.reports.index', array_merge(request()->except(['type', 'page']), ['type' => $tabType])) }}"
                           class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 {{ $type === $tabType ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/25' : 'text-gray-600 hover:bg-gray-100' }}">
                            {{ $tabLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="min-w-[150px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">From</label>
                        <div class="relative mt-1.5">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <input name="date_from" type="date" value="{{ $dateFrom }}" class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm text-sm" />
                        </div>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">To</label>
                        <div class="relative mt-1.5">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <input name="date_to" type="date" value="{{ $dateTo }}" class="block w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm text-sm" />
                        </div>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</label>
                        <select name="department_id" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm text-sm py-2.5">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</label>
                        <select name="status" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm text-sm py-2.5">
                            <option value="">All Status</option>
                            @foreach(\App\Enums\TaskStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[160px]">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</label>
                        <select name="employee_id" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm text-sm py-2.5">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-sm font-medium text-white shadow-md shadow-amber-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        Filter
                    </button>
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 p-5 text-white shadow-lg shadow-indigo-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-indigo-100">Total Tasks</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['total'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg shadow-emerald-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-emerald-100">Completed</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['completed'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-5 text-white shadow-lg shadow-sky-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-sky-100">In Progress</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['in_progress'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-5 text-white shadow-lg shadow-rose-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-rose-100">Overdue</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['overdue'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-600 p-5 text-white shadow-lg shadow-amber-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-amber-100">Under Review</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['under_review'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 p-5 text-white shadow-lg shadow-orange-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-orange-100">Changes Requested</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['changes_requested'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-500 to-gray-700 p-5 text-white shadow-lg shadow-gray-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-gray-200">New</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $summary['pending'] }}</div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 p-5 text-white shadow-lg shadow-emerald-500/20">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                    <div class="relative">
                        <div class="text-xs font-medium text-emerald-100">Completion</div>
                        <div class="mt-1.5 text-2xl font-extrabold">{{ $completionRate }}%</div>
                    </div>
                </div>
            </div>

            <!-- Export -->
            <div class="flex gap-3">
                <a href="{{ route('admin.reports.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 text-sm font-medium text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Export Excel
                </a>
                @if($type === 'overview')
                    <a href="{{ route('admin.reports.export-pdf', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 text-sm font-medium text-white shadow-md shadow-rose-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        Export PDF
                    </a>
                @endif
            </div>

            {{-- OVERVIEW TAB --}}
            @if($type === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-5">Tasks by Status</h3>
                            <div class="space-y-4">
                                @foreach($statusBreakdown['labels'] as $i => $label)
                                    @php $count = $statusBreakdown['data'][$i]; @endphp
                                    <div>
                                        <div class="flex justify-between text-sm mb-1.5">
                                            <span class="text-gray-600 font-medium">{{ \App\Enums\TaskStatus::tryFrom($label)?->label() ?? $label }}</span>
                                            <span class="font-bold text-gray-900">{{ $count }}</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-indigo-500 to-violet-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $summary['total'] > 0 ? round(($count / $summary['total']) * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-sky-500 to-blue-600"></div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-5">Tasks by Department</h3>
                            <div class="space-y-4">
                                @foreach($departmentReports as $deptData)
                                    <div>
                                        <div class="flex justify-between text-sm mb-1.5">
                                            <span class="text-gray-600 font-medium">{{ $deptData['department']->name }}</span>
                                            <span class="font-bold text-gray-900">{{ $deptData['total'] }} total, {{ $deptData['completed'] }} done ({{ $deptData['completion_rate'] }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                                            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $deptData['completion_rate'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Task Details</h3>
                        <div class="space-y-3">
                            @forelse($reportTasks->take(50) as $task)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex-shrink-0 w-20 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-xs font-bold text-indigo-600">{{ $task->code() }}</span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $task->title }}</p>
                                            <p class="text-xs text-gray-500">Created {{ $task->created_at?->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <span class="hidden sm:inline text-xs text-gray-500">{{ $task->updates_count }} updates</span>
                                        <span class="hidden sm:inline text-xs {{ $task->isOverdue() && !$task->isCompleted() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ $task->due_date?->format('M d, Y') ?? '-' }}</span>
                                        @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <p class="text-sm text-gray-500">No tasks found.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            {{-- EMPLOYEE TAB --}}
            @if($type === 'employee' && isset($performance))
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $performance['employee']->name }}</h3>
                        <p class="text-sm text-gray-500 mb-6">{{ $employee?->department?->name ?? 'Employee' }}</p>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            @include('partials.metric-card', ['label' => 'Total Tasks', 'value' => $performance['total'], 'color' => 'gray'])
                            @include('partials.metric-card', ['label' => 'Completed', 'value' => $performance['completed'], 'color' => 'green'])
                            @include('partials.metric-card', ['label' => 'Completion Rate', 'value' => $performance['completion_rate'].'%', 'color' => 'indigo'])
                            @include('partials.metric-card', ['label' => 'Avg. Days', 'value' => $performance['average_completion_days'], 'color' => 'blue'])
                            @include('partials.metric-card', ['label' => 'Updates', 'value' => $performance['updates'], 'color' => 'gray'])
                            @include('partials.metric-card', ['label' => 'Review Cycles', 'value' => $performance['review_cycles'], 'color' => 'yellow'])
                            @include('partials.metric-card', ['label' => 'Changes Requested', 'value' => $performance['changes_requested'], 'color' => 'red'])
                            @include('partials.metric-card', ['label' => 'Resubmissions', 'value' => $performance['resubmissions'], 'color' => 'purple'])
                        </div>

                        <div class="space-y-3">
                            @forelse($taskRows as $row)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $row['task']->title }}</p>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                            <span>{{ $row['updates'] }} updates</span>
                                            <span>{{ $row['review_cycles'] }} reviews</span>
                                            <span class="{{ $row['changes_requested'] > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $row['changes_requested'] }} changes</span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="text-sm font-bold text-gray-900">{{ $row['completion_days'] ?? '-' }}</span>
                                        <p class="text-xs text-gray-500">days</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10"><p class="text-sm text-gray-500">No tasks found.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @elseif($type === 'employee')
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                    <div class="p-6">
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-100 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">Select an employee from the filter above</p>
                            <p class="text-xs text-gray-500 mt-1">Choose a team member to view their individual performance report</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- DEPARTMENT TAB --}}
            @if($type === 'department' && isset($departmentReports))
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-sky-500 to-blue-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Department Performance</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Members</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Completed</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">In Progress</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Under Review</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Changes</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Overdue</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($departmentReports as $dept)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $dept['department']->name }}</td>
                                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $dept['employees'] }}</td>
                                            <td class="px-4 py-3 text-sm text-center text-gray-900 font-medium">{{ $dept['total'] }}</td>
                                            <td class="px-4 py-3 text-sm text-center text-emerald-600 font-medium">{{ $dept['completed'] }}</td>
                                            <td class="px-4 py-3 text-sm text-center text-sky-600 font-medium">{{ $dept['in_progress'] }}</td>
                                            <td class="px-4 py-3 text-sm text-center text-amber-600 font-medium">{{ $dept['under_review'] }}</td>
                                            <td class="px-4 py-3 text-sm text-center text-orange-600 font-medium">{{ $dept['changes_requested'] }}</td>
                                            <td class="px-4 py-3 text-sm text-center {{ $dept['overdue'] > 0 ? 'text-red-600 font-bold' : 'text-gray-500' }}">{{ $dept['overdue'] }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2 justify-center">
                                                    <div class="w-16 bg-gray-100 rounded-full h-2">
                                                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-2 rounded-full" style="width: {{ $dept['completion_rate'] }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-bold text-gray-900">{{ $dept['completion_rate'] }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="px-4 py-8 text-sm text-gray-500 text-center">No departments found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- REVIEW TAB --}}
            @if($type === 'review' && isset($reviews))
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-purple-500 to-violet-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Review History</h3>
                        <div class="space-y-3">
                            @forelse($reviews as $review)
                                <div class="p-4 rounded-xl border border-gray-100 hover:border-purple-200 hover:bg-purple-50/50 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-xs font-bold text-purple-600">#{{ $review['id'] }}</span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $review['task']->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $review['reviewer']?->name ?? '-' }}</p>
                                            </div>
                                        </div>
                                        @include('partials.status-badge', [
                                            'label' => $review['decision'],
                                            'color' => $review['review']->decision === 'approved' ? 'green' : 'red',
                                        ])
                                    </div>
                                    @if($review['comment'])
                                        <p class="text-sm text-gray-600 ml-11 italic">"{{ $review['comment'] }}"</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-2 ml-11">{{ $review['reviewed_at'] }}</p>
                                </div>
                            @empty
                                <div class="text-center py-10"><p class="text-sm text-gray-500">No reviews found.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            {{-- OVERDUE TAB --}}
            @if($type === 'overdue' && isset($overdueTasks))
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-rose-500 to-red-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Overdue Tasks</h3>
                        <div class="space-y-3">
                            @forelse($overdueTasks as $task)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-red-100 hover:border-red-300 hover:bg-red-50/50 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center text-red-600">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900">{{ $task->title }}</p>
                                            <p class="text-xs text-gray-500">{{ $task->assignees->pluck('name')->implode(', ') ?: '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <span class="hidden sm:inline text-xs font-medium text-gray-500">{{ $task->getPriorityLabel() }}</span>
                                        @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
                                        <span class="text-sm font-bold text-red-600">{{ $task->due_date?->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700">No overdue tasks!</p>
                                    <p class="text-xs text-gray-500">All tasks are on schedule.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            {{-- UPDATES TAB --}}
            @if($type === 'updates' && isset($updates))
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-amber-500 to-orange-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Updates & Revisions</h3>
                        <div class="space-y-3">
                            @forelse($updates as $row)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-amber-200 hover:bg-amber-50/50 transition">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $row['title'] }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $row['assignee'] }} &middot; {{ $row['status'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-4 flex-shrink-0 text-sm">
                                        <div class="text-center">
                                            <p class="font-bold text-gray-900">{{ $row['updates'] }}</p>
                                            <p class="text-xs text-gray-500">Updates</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-bold text-gray-900">{{ $row['review_cycles'] }}</p>
                                            <p class="text-xs text-gray-500">Reviews</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-bold {{ $row['changes_requested'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $row['changes_requested'] }}</p>
                                            <p class="text-xs text-gray-500">Changes</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-bold text-gray-900">{{ $row['resubmissions'] }}</p>
                                            <p class="text-xs text-gray-500">Resubmit</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10"><p class="text-sm text-gray-500">No updates found.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            {{-- TURNAROUND TAB --}}
            @if($type === 'turnaround' && isset($turnaround))
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-sky-500 to-blue-600"></div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Turnaround Time</h3>
                        <div class="space-y-3">
                            @forelse($turnaround as $row)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-sky-200 hover:bg-sky-50/50 transition">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $row['title'] }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $row['assignee'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-6 flex-shrink-0 text-xs text-gray-500">
                                        <div class="text-center">
                                            <p class="font-medium text-gray-900">{{ $row['created_at'] }}</p>
                                            <p>Created</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-medium text-gray-900">{{ $row['start_date'] ?? '-' }}</p>
                                            <p>Started</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-medium text-gray-900">{{ $row['completed_at'] }}</p>
                                            <p>Completed</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-lg font-extrabold text-indigo-600">{{ $row['days'] ?? '-' }}</p>
                                            <p class="font-medium">Days</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10"><p class="text-sm text-gray-500">No completed tasks found.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>