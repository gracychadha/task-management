@php
    $history = $task->reviews->sortByDesc('review_number');
@endphp

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
        <h4 class="text-sm font-medium text-gray-900">Review History ({{ $history->count() }})</h4>
        <span class="text-xs text-gray-500">{{ $task->reviewCyclesCount() }} review cycle(s)</span>
    </div>

    @forelse($history as $review)
        <div class="relative pl-5 pb-4">
            <div class="absolute left-0 top-1.5 w-2 h-2 rounded-full
                {{ $review->decision === 'approved' ? 'bg-emerald-500' : 'bg-red-400' }}"></div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-900">Review #{{ $review->review_number }}</span>
                @include('partials.status-badge', [
                    'label' => $review->decision === 'approved' ? 'Approved' : 'Changes Requested',
                    'color' => $review->decision === 'approved' ? 'green' : 'red',
                ])
                <span class="text-xs text-gray-500">{{ $review->reviewed_at?->format('M d, Y H:i') }}</span>
            </div>
            <div class="text-sm text-gray-700 mt-1">
                By <span class="font-medium">{{ $review->reviewer?->name ?? '-' }}</span>
                @if($review->comment)
                    <div class="mt-1 p-2 bg-gray-50 rounded-md whitespace-pre-line text-gray-600">{{ $review->comment }}</div>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">No reviews yet.</p>
    @endforelse
    </div>
</div>