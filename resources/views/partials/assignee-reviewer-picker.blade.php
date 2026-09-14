@php
    $pickerDepartments = $departments->map(fn ($d) => [
        'id' => $d->id,
        'name' => $d->name,
        'users' => $d->users->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'initials' => $u->initials(),
        ])->values()->all(),
    ])->values()->all();

    $pickerReviewers = $reviewers->map(fn ($r) => [
        'id' => $r->id,
        'name' => $r->name,
        'initials' => $r->initials(),
        'department_id' => $r->department_id,
    ])->values()->all();

    $managerDefaultDept = auth()->user()->isManager() ? auth()->user()->department_id : null;

    $pickerConfig = [
        'departments' => $pickerDepartments,
        'reviewers' => $pickerReviewers,
        'selectedAssignees' => collect(old('assigned_to', []))->map(fn ($id) => (int) $id)->values()->all(),
        'selectedReviewer' => old('reviewer_id') ? (int) old('reviewer_id') : null,
        'departmentId' => old('department_id', $managerDefaultDept),
        'reviewerDepartmentId' => null,
    ];
@endphp

<div x-data='(() => {
    const c = @json($pickerConfig);
    return {
        departments: c.departments || [],
        reviewers: c.reviewers || [],
        assigneeIds: c.selectedAssignees || [],
        reviewerId: c.selectedReviewer || null,
        departmentId: c.departmentId || "",
        reviewerDepartmentId: c.reviewerDepartmentId || "",
        assigneeSearch: "",
        reviewerSearch: "",
        assigneeOpen: false,
        reviewerOpen: false,
        get selectedAssignees() {
            return this.departments.flatMap((d) => d.users || []).filter((u) => this.assigneeIds.includes(u.id));
        },
        get selectedReviewerName() {
            const r = this.reviewers.find((r) => r.id === this.reviewerId);
            return r ? r.name : "";
        },
        get selectedReviewerInitials() {
            const r = this.reviewers.find((r) => r.id === this.reviewerId);
            if (!r || !r.name) return "";
            return String(r.name).split(" ").map((w) => w[0] || "").join("").slice(0, 2).toUpperCase();
        },
        assigneeMatches(name) {
            const q = this.assigneeSearch.toLowerCase();
            return !q || String(name).toLowerCase().includes(q);
        },
        reviewerMatches(name) {
            const q = this.reviewerSearch.toLowerCase();
            return !q || String(name).toLowerCase().includes(q);
        },
        toggleAssignee(id) {
            this.assigneeIds = this.assigneeIds.includes(id)
                ? this.assigneeIds.filter((i) => i !== id)
                : [...this.assigneeIds, id];
            if (this.assigneeIds.includes(this.reviewerId)) this.reviewerId = null;
        },
        removeAssignee(id) {
            this.assigneeIds = this.assigneeIds.filter((i) => i !== id);
        },
        clearAssignees() {
            this.assigneeIds = [];
        },
        assignAllVisible() {
            let ids = [...this.assigneeIds];
            this.departments
                .filter((d) => !this.departmentId || String(d.id) === String(this.departmentId))
                .flatMap((d) => d.users || [])
                .forEach((u) => {
                    if (!ids.includes(u.id)) ids.push(u.id);
                });
            this.assigneeIds = ids;
            if (this.assigneeIds.includes(this.reviewerId)) this.reviewerId = null;
        },
        init() {
            this.$watch("reviewerDepartmentId", (v) => {
                if (!v) return;
                const r = this.reviewers.find((r) => r.id === this.reviewerId);
                if (r && String(r.department_id ?? "") !== String(v)) this.reviewerId = null;
            });
        },
    };
})()'>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="department_id" value="Assignee Department" />
            <select id="department_id" name="department_id" x-model="departmentId" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm block w-full">
                <option value="">— All departments —</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id', $managerDefaultDept) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Select the team that will work on this task.</p>
        </div>
        <div>
            <x-input-label for="reviewer_department_id" value="Reviewer Department (optional)" />
            <select id="reviewer_department_id" x-model="reviewerDepartmentId" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm block w-full">
                <option value="">— All departments —</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">The reviewer can belong to a different department.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        {{-- Assignees --}}
        <div class="relative" @click.outside="assigneeOpen = false">
            <x-input-label value="Assignees" />
            <button type="button" @click="assigneeOpen = !assigneeOpen"
                    class="mt-1 w-full min-h-[46px] px-3.5 py-2.5 border border-gray-300 rounded-xl bg-white text-left text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none transition hover:border-gray-400">
                <template x-if="selectedAssignees.length === 0">
                    <span class="flex items-center justify-between text-gray-400">
                        <span>Select assignees...</span>
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </span>
                </template>
                <template x-if="selectedAssignees.length > 0">
                    <span class="flex items-start justify-between gap-2">
                        <span class="flex flex-wrap gap-1.5 flex-1">
                            <template x-for="u in selectedAssignees" :key="u.id">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                    <span class="w-4 h-4 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-[7px] font-bold flex items-center justify-center" x-text="u.initials"></span>
                                    <span x-text="u.name"></span>
                                    <svg @click.stop.prevent="removeAssignee(u.id)" class="w-3 h-3 text-indigo-400 hover:text-red-500 transition cursor-pointer" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </span>
                            </template>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 mt-1 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </span>
                </template>
            </button>

            <div x-show="assigneeOpen" x-transition x-cloak class="absolute z-50 bottom-full mb-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                <div class="p-3 border-b border-gray-100">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" x-model="assigneeSearch" placeholder="Search employees..." class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>
                <div class="max-h-56 overflow-y-auto p-1.5">
                    @forelse($pickerDepartments as $dept)
                        <div x-show="!departmentId || departmentId == {{ $dept['id'] }}">
                            <div class="px-3 py-1.5 mt-1 text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ $dept['name'] }}</div>
                            @forelse($dept['users'] as $u)
                                <label x-show="assigneeMatches({{ json_encode($u['name']) }})" data-department="{{ $dept['id'] }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-indigo-50/60 cursor-pointer transition">
                                    <input type="checkbox" :checked="assigneeIds.includes({{ $u['id'] }})" @change="toggleAssignee({{ $u['id'] }})" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ $u['initials'] }}</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $u['name'] }}</span>
                                </label>
                            @empty
                                <p class="px-3 py-2 text-xs text-gray-400 italic">No employees in this department</p>
                            @endforelse
                        </div>
                    @empty
                        <p class="px-3 py-3 text-xs text-gray-400">No departments available.</p>
                    @endforelse
                </div>
                <div class="flex items-center justify-between px-3 py-2.5 border-t border-gray-100 bg-gray-50">
                    @if($showAssignAll ?? false)
                        <button type="button" @click="assignAllVisible()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Assign to all department members</button>
                    @else
                        <span></span>
                    @endif
                    <button type="button" @click="clearAssignees()" class="text-xs font-medium text-gray-500 hover:text-red-600 transition">Clear</button>
                </div>
            </div>

            <template x-for="u in selectedAssignees" :key="u.id">
                <input type="hidden" name="assigned_to[]" :value="u.id">
            </template>
        </div>

        {{-- Reviewer --}}
        <div class="relative" @click.outside="reviewerOpen = false">
            <x-input-label value="Reviewer (optional)" />
            <button type="button" @click="reviewerOpen = !reviewerOpen"
                    class="mt-1 w-full min-h-[46px] px-3.5 py-2.5 border border-gray-300 rounded-xl bg-white text-left text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none transition hover:border-gray-400">
                <template x-if="selectedReviewerName">
                    <span class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-2 text-gray-800 font-medium">
                            <span class="w-5 h-5 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white text-[8px] font-bold flex items-center justify-center" x-text="selectedReviewerInitials"></span>
                            <span x-text="selectedReviewerName"></span>
                        </span>
                        <svg @click.stop.prevent="reviewerId = null" class="w-3.5 h-3.5 text-gray-400 hover:text-red-500 transition cursor-pointer" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </span>
                </template>
                <template x-if="!selectedReviewerName">
                    <span class="flex items-center justify-between text-gray-400">
                        <span>Assign after submission</span>
                        <svg class="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </span>
                </template>
            </button>

            <div x-show="reviewerOpen" x-transition x-cloak class="absolute z-50 bottom-full mb-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                <div class="p-3 border-b border-gray-100">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" x-model="reviewerSearch" placeholder="Search reviewers..." class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>
                <div class="max-h-56 overflow-y-auto p-1.5">
                    @php
                        $reviewerGroups = collect($pickerReviewers)
                            ->groupBy(fn ($r) => $r['department_id'] ?? 'unassigned');
                    @endphp
                    @forelse($reviewerGroups as $deptKey => $group)
                        @php
                            $group = $group->values();
                            $deptName = $deptKey === 'unassigned'
                                ? 'Unassigned'
                                : ($departments->firstWhere('id', (int) $deptKey)?->name ?: 'Unassigned');
                        @endphp
                        <div x-show="!reviewerDepartmentId || reviewerDepartmentId == {{ $deptKey === 'unassigned' ? 'null' : $deptKey }}"
                             class="px-3 py-1.5 mt-1 text-[11px] font-bold uppercase tracking-wide text-emerald-700/80">{{ $deptName }}</div>
                        @foreach($group as $r)
                            <label x-show="(!reviewerDepartmentId || reviewerDepartmentId == {{ $r['department_id'] ?? 'null' }}) && !assigneeIds.includes({{ $r['id'] }}) && reviewerMatches({{ json_encode($r['name']) }})"
                                   data-department="{{ $r['department_id'] ?? '' }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-emerald-50/60 cursor-pointer transition">
                                <input type="radio" :checked="reviewerId === {{ $r['id'] }}" @change="reviewerId = {{ $r['id'] }}" name="reviewer_picker" class="border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ $r['initials'] }}</span>
                                <span class="text-sm font-medium text-gray-700 min-w-0 flex-1 truncate">{{ $r['name'] }}</span>
                            </label>
                        @endforeach
                    @empty
                        <p class="px-3 py-3 text-xs text-gray-400">No reviewers available.</p>
                    @endforelse
                    <p x-show="assigneeIds.length > 0" class="px-3 py-2 text-[11px] text-gray-400">Employees selected as assignees are hidden.</p>
                </div>
            </div>

            <input type="hidden" name="reviewer_id" :value="reviewerId || ''">
        </div>
    </div>
</div>