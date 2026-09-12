@php
    $steps = collect([
        ['key' => 'new', 'label' => 'New'],
        ['key' => 'in_progress', 'label' => 'In Progress'],
        ['key' => 'under_review', 'label' => 'Under Review'],
        ['key' => 'changes_requested', 'label' => 'Changes Requested'],
    ]);

    if ($task->changesRequestedCount() > 0 || $task->reviews->count() >= 2) {
        $steps->push(['key' => 'in_progress_revised', 'label' => 'In Progress']);
        $steps->push(['key' => 'under_review_revised', 'label' => 'Under Review']);
    }

    $steps->push(['key' => 'completed', 'label' => 'Completed']);
    $steps = $steps->values()->all();

    $currentIndex = match (true) {
        $task->isNew() => 0,
        $task->isInProgress() => $task->changesRequestedCount() > 0 ? 4 : 1,
        $task->isUnderReview() => $task->reviews->count() >= 2 ? 5 : 2,
        $task->isChangesRequested() => 3,
        $task->isCompleted() => count($steps) - 1,
        default => null,
    };
@endphp

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-medium text-gray-900">Progress</h4>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">Current:</span>
            @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
        </div>
    </div>

    <ol class="flex items-center gap-0 overflow-x-auto pb-2">
        @foreach($steps as $step)
            @php
                $stepIndex = $loop->index;
                $passed = $currentIndex !== null && $stepIndex < $currentIndex;
                $active = $stepIndex === $currentIndex;
            @endphp
            <li class="flex items-center shrink-0">
                <div @class([
                    'flex items-center gap-2 rounded-full px-3 py-1.5',
                    'bg-gray-100 text-gray-500' => ! $passed && ! $active,
                    'bg-emerald-100 text-emerald-800' => $passed,
                    'bg-indigo-100 text-indigo-800 font-semibold' => $active,
                ])>
                    @if($passed)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @endif
                    <span class="text-xs whitespace-nowrap">{{ $step['label'] }}</span>
                </div>
                @if(! $loop->last)
                    <div @class([
                        'h-px w-6',
                        'bg-emerald-300' => $currentIndex !== null && $stepIndex < $currentIndex,
                        'bg-gray-200' => $currentIndex === null || $stepIndex >= $currentIndex,
                    ])></div>
                @endif
            </li>
        @endforeach
    </ol>

    <div class="mt-3 text-xs text-gray-500">
        Review cycles: {{ $task->reviewCyclesCount() }} &middot; Changes requested: {{ $task->changesRequestedCount() }}
        &middot; Updates: {{ $task->updates_count }} &middot; Resubmissions: {{ $task->resubmissions() }}
    </div>
    </div>
</div>