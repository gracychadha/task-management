<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->title }}</h2>
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

                        <!-- Status Update -->
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Update Status</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Enums\TaskStatus::cases() as $status)
                                    <button type="button"
                                            data-status="{{ $status->value }}"
                                            class="px-3 py-1.5 rounded-md text-xs font-medium {{ $task->status === $status->value ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                            onclick="updateStatus('{{ $status->value }}')">
                                        {{ $status->label() }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

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
                            <div class="flex justify-between"><span class="text-gray-500">Created by</span><span class="text-gray-900">{{ $task->creator?->name ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Assignees</span><span class="text-gray-900">{{ $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Due Date</span><span class="{{ $task->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">{{ $task->due_date?->format('M d, Y') ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Est. Hours</span><span class="text-gray-900">{{ $task->estimated_hours ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Actual Hours</span><span class="text-gray-900">{{ $task->actual_hours ?? '-' }}</span></div>
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
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateStatus(status) {
            fetch('{{ route("tasks.update-status", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status }),
            }).then(() => location.reload());
        }
    </script>
    @endpush
</x-app-layout>
