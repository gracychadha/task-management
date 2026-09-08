<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->title }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('manager.team-tasks.edit', $task) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Edit</a>
                <form method="POST" action="{{ route('manager.team-tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <x-danger-button type="submit">Delete</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->status === 'done') bg-green-100 text-green-800
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $task->getStatusLabel() }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->priority === 'urgent') bg-red-100 text-red-800
                                @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                @elseif($task->priority === 'medium') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $task->getPriorityLabel() }}
                            </span>
                        </div>

                        @if($task->description)
                            <div class="text-gray-700 prose prose-sm max-w-none mb-4">{!! nl2br(e($task->description)) !!}</div>
                        @endif

                        <!-- Comments -->
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Comments ({{ $task->comments->count() }})</h4>
                            <div class="space-y-3 mb-4">
                                @forelse($task->comments as $comment)
                                    <div class="flex gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-xs font-medium shrink-0">{{ $comment->user->initials() }}</div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="text-sm text-gray-700 mt-1">{{ $comment->comment }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No comments yet.</p>
                                @endforelse
                            </div>

                            <form method="POST" action="{{ route('tasks.comments.store', $task) }}">
                                @csrf
                                <textarea name="comment" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full text-sm" placeholder="Add a comment..." required></textarea>
                                <div class="flex justify-end mt-2">
                                    <x-primary-button type="submit" class="text-xs">Post Comment</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Details</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Assignees</span><span class="text-gray-900">{{ $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Created by</span><span class="text-gray-900">{{ $task->creator?->name ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Start Date</span><span class="text-gray-900">{{ $task->start_date?->format('M d, Y') ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Due Date</span><span class="{{ $task->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">{{ $task->due_date?->format('M d, Y') ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Est. Hours</span><span class="text-gray-900">{{ $task->estimated_hours ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
