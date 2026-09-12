<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->code() }} &mdash; {{ $task->title }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.tasks.edit', $task) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Edit</a>
                <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <x-danger-button type="submit">Delete</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    @include('partials.task-progress')

                    <!-- Task Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center gap-2 mb-4">
                            @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($task->priority === 'urgent') bg-red-100 text-red-800
                                @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                @elseif($task->priority === 'medium') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $task->getPriorityLabel() }}
                            </span>
                            @if($task->isCompleted())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Completed {{ $task->completed_at?->format('M d, Y') }}</span>
                            @elseif($task->isOverdue())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Overdue</span>
                            @endif
                        </div>

                        @if($task->description)
                            <div class="text-gray-700 prose prose-sm max-w-none mb-4">{!! nl2br(e($task->description)) !!}</div>
                        @endif

                        @if($task->labels->count())
                            <div class="flex flex-wrap gap-1 mb-4">
                                @foreach($task->labels as $label)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $label->color ?? '#e5e7eb' }}; color: #1f2937;">{{ $label->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Review Workflow -->
                        @include('partials.review-workflow', ['reviewerCandidates' => $reviewerCandidates ?? collect()])

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

                    @include('partials.review-history')

                    @include('partials.activity-timeline')
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Task Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Details</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Assignees</span>
                                <div class="flex flex-wrap gap-1 justify-end">
                                    @forelse($task->assignees as $assignee)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs">{{ $assignee->name }}</span>
                                    @empty
                                        <span class="text-gray-900">Unassigned</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Created by</span>
                                <span class="text-gray-900">{{ $task->creator?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Reviewer</span>
                                <span class="text-gray-900">{{ $task->reviewer?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Department</span>
                                <span class="text-gray-900">{{ $task->department?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Start Date</span>
                                <span class="text-gray-900">{{ $task->start_date?->format('M d, Y') ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Due Date</span>
                                <span class="{{ $task->isOverdue() && ! $task->isCompleted() ? 'text-red-600 font-medium' : 'text-gray-900' }}">{{ $task->due_date?->format('M d, Y') ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Est. Hours</span>
                                <span class="text-gray-900">{{ $task->estimated_hours ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Actual Hours</span>
                                <span class="text-gray-900">{{ $task->actual_hours ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Task Stats</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="p-3 bg-gray-50 rounded-md text-center">
                                <div class="text-xl font-semibold text-gray-900">{{ $task->updates_count }}</div>
                                <div class="text-xs text-gray-500">Updates</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-md text-center">
                                <div class="text-xl font-semibold text-gray-900">{{ $task->reviewCyclesCount() }}</div>
                                <div class="text-xs text-gray-500">Review Cycles</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-md text-center">
                                <div class="text-xl font-semibold text-gray-900">{{ $task->changesRequestedCount() }}</div>
                                <div class="text-xs text-gray-500">Changes Requested</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-md text-center">
                                <div class="text-xl font-semibold text-gray-900">{{ $task->resubmissions() }}</div>
                                <div class="text-xs text-gray-500">Resubmissions</div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Attachments ({{ $task->attachments->count() }})</h4>
                        <div class="space-y-2 mb-4">
                            @forelse($task->attachments as $attachment)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500 truncate">{{ $attachment->file_name }}</a>
                                    <span class="text-xs text-gray-500">{{ round($attachment->file_size / 1024, 1) }}KB</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No attachments.</p>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" required>
                            <div class="flex justify-end mt-2">
                                <x-primary-button type="submit" class="text-xs">Upload</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Subtasks -->
                    @if($task->subtasks->count())
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Subtasks ({{ $task->subtasks->count() }})</h4>
                            <div class="space-y-2">
                                @foreach($task->subtasks as $sub)
                                    <a href="{{ route('admin.tasks.show', $sub) }}" class="flex items-center gap-2 py-2 border-b border-gray-100 last:border-0 hover:text-indigo-600">
                                        @include('partials.status-badge', ['label' => $sub->getStatusLabel(), 'color' => $sub->getStatusColor()])
                                        <span class="text-sm text-gray-900">{{ $sub->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>