<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Task Details</p>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $task->code() }} &mdash; {{ $task->title }}</h1>
                </div>
            </div>
            <a href="{{ route('employee.my-tasks') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to My Tasks
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    @include('partials.task-progress')

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                        <div class="p-6">
                            <div class="flex items-center flex-wrap gap-2 mb-4">
                                @include('partials.status-badge', ['label' => $task->getStatusLabel(), 'color' => $task->getStatusColor()])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($task->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($task->priority === 'medium') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $task->getPriorityLabel() }}
                                </span>
                                @if($task->isOverdue() && ! $task->isCompleted())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Overdue</span>
                                @endif
                            </div>

                            @if($task->description)
                                <div class="text-gray-700 prose prose-sm max-w-none mb-4">{!! nl2br(e($task->description)) !!}</div>
                            @endif

                            @if($task->labels->count())
                                <div class="flex flex-wrap gap-1 mb-4">
                                    @foreach($task->labels as $label)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $label->color ?? '#e5e7eb' }}">{{ $label->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @include('partials.review-workflow')

                            @if($task->isEditableByAssignedEmployee())
                            <div class="border-t border-gray-200 pt-5 mt-5">
                                <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </span>
                                    Update Task
                                </h4>

                                @if($task->status === 'new')
                                    <div class="text-sm text-gray-600 mb-3">
                                        Start working on this task to record your first update.
                                    </div>
                                    <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="in_progress">
                                        <div>
                                            <x-input-label value="Note (optional)" />
                                            <textarea name="update_message" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm mt-1 block w-full text-sm" placeholder="What are you starting on?"></textarea>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                                Start Task
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <p class="text-sm text-gray-600 mb-3">
                                        {{ $task->status === 'changes_requested' ? 'Changes were requested by the reviewer. Address them, then resubmit for review.' : 'Submit your work for review to advance the task.' }}
                                    </p>
                                    <form method="POST" action="{{ route('tasks.update-status', $task) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="under_review">
                                        <div>
                                            <x-input-label value="Update message" />
                                            <textarea name="update_message" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm mt-1 block w-full text-sm" placeholder="Describe what you completed in this update..." required></textarea>
                                            <x-input-error :messages="$errors->get('update_message')" class="mt-2" />
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <x-input-label value="Submission link (optional)" />
                                                <input type="url" name="submission_link" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm mt-1 block w-full text-sm" placeholder="https://example.com/deliverable">
                                                <x-input-error :messages="$errors->get('submission_link')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label value="Proof image (optional)" />
                                                <input type="file" name="submission_image" accept="image/*" class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                                <x-input-error :messages="$errors->get('submission_image')" class="mt-2" />
                                            </div>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-sm font-medium text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"></polyline><line x1="4" y1="20" x2="21" y2="3"></line><polyline points="21 16 21 21 16 21"></polyline><line x1="15" y1="15" x2="21" y2="21"></line><line x1="4" y1="4" x2="9" y2="9"></line></svg>
                                                Submit for Review
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                            @elseif($task->isCompleted())
                            <div class="border-t border-gray-200 pt-5 mt-5">
                                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-sm text-emerald-800">
                                    <svg class="w-5 h-5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    This task is complete. It was approved on {{ $task->completed_at?->format('M d, Y H:i') }}.
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @include('partials.review-history')

                    @include('partials.activity-timeline')

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                        <div class="p-6">
                            <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>
                                </span>
                                Comments ({{ $task->comments->count() }})
                            </h4>
                            <div class="space-y-3 mb-4">
                                @forelse($task->comments as $comment)
                                    <div class="flex gap-3 p-3 rounded-xl bg-gray-50">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xs font-medium shrink-0">{{ $comment->user->initials() }}</div>
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
                                <textarea name="comment" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm mt-1 block w-full text-sm" placeholder="Add a comment..." required></textarea>
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">Post Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                        <div class="p-6">
                            <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                </span>
                                Details
                            </h4>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-gray-500">Created by</span><span class="text-gray-900">{{ $task->creator?->name ?? '-' }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Assignees</span><span class="text-gray-900">{{ $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned' }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Reviewer</span><span class="text-gray-900">{{ $task->reviewer?->name ?? '—' }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Department</span><span class="text-gray-900">{{ $task->department?->name ?? '—' }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Due Date</span><span class="{{ $task->isOverdue() && ! $task->isCompleted() ? 'text-red-600' : 'text-gray-900' }}">{{ $task->due_date?->format('M d, Y') ?? '-' }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Est. Hours</span><span class="text-gray-900">{{ $task->estimated_hours ?? '-' }}</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Actual Hours</span><span class="text-gray-900">{{ $task->actual_hours ?? '-' }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                        <div class="p-6">
                            <h4 class="text-sm font-bold text-gray-900 mb-3">My Stats</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl text-center">
                                    <div class="text-xl font-bold text-gray-900">{{ $task->updates_count }}</div>
                                    <div class="text-xs text-gray-500">Updates</div>
                                </div>
                                <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl text-center">
                                    <div class="text-xl font-bold text-gray-900">{{ $task->resubmissions() }}</div>
                                    <div class="text-xs text-gray-500">Resubmissions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600"></div>
                        <div class="p-6">
                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-violet-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"></path></svg>
                                </span>
                                Attachments ({{ $task->attachments->count() }})
                            </h4>
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
                                <input type="file" name="file" class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" required>
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-sm font-medium text-white shadow-md shadow-indigo-500/25 hover:shadow-lg hover:-translate-y-0.5 transition">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>