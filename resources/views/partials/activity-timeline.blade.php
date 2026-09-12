@php
    use Spatie\Activitylog\Models\Activity;
    $activities = Activity::forSubject($task)->with('causer')->orderByDesc('id')->limit(15)->get();
@endphp

<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
        <h4 class="text-sm font-medium text-gray-900">Activity</h4>
        <span class="text-xs text-gray-500">Last {{ $activities->count() }} event(s)</span>
    </div>

    @forelse($activities as $activity)
        <div class="flex gap-3 pb-3">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-xs font-medium shrink-0">
                {{ $activity->causer?->initials() ?? '–' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-800">
                    <span class="font-medium">{{ $activity->causer?->name ?? 'System' }}</span>
                    <span class="text-gray-500">{{ $activity->description }}</span>
                </div>
                <div class="text-xs text-gray-500">{{ $activity->created_at?->diffForHumans() }}</div>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">No activity recorded yet.</p>
    @endforelse
    </div>
</div>