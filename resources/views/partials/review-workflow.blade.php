@php
    $user = Auth::user();
    $canAdmin = $user->isAdmin();
    $canManage = $user->isAdmin() || ($user->isManager() && (
        $task->created_by === $user->id
        || $task->assignees->contains('id', $user->id)
        || ($user->department_id && $task->assignees->contains(fn ($a) => $a->department_id === $user->department_id))
    ));
    $isReviewer = $task->reviewer_id === $user->id;
    $candidates = $reviewerCandidates ?? collect();
@endphp

@if($task->isInReviewCycle() || $canManage || $isReviewer)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-sm font-medium text-gray-900">Review Workflow</h4>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $task->getReviewStatusColor() }}">
                {{ $task->getReviewStatusLabel() }}
            </span>
        </div>

        @if($task->reviewer)
            <div class="text-sm text-gray-700 mb-3">
                <span class="text-gray-500">Reviewer:</span>
                <span class="font-medium text-gray-900">{{ $task->reviewer->name }}</span>
            </div>
        @endif

        @if($task->hasSubmission())
            <div class="text-sm text-gray-700 mb-3 p-3 bg-gray-50 rounded-md">
                <div class="font-medium text-gray-900 mb-1">Submission evidence</div>
                @if($task->submission_link)
                    <div class="mb-1">
                        <a href="{{ $task->submission_link }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-500 break-all">{{ $task->submission_link }}</a>
                    </div>
                @endif
                @if($task->submissionAttachment)
                    @if(str_starts_with($task->submissionAttachment->file_type ?? '', 'image/'))
                        <a href="{{ Storage::url($task->submissionAttachment->file_path) }}" target="_blank" rel="noopener">
                            <img src="{{ Storage::url($task->submissionAttachment->file_path) }}" alt="Submission proof" class="mt-2 max-h-64 rounded-md border border-gray-200">
                        </a>
                    @else
                        <a href="{{ Storage::url($task->submissionAttachment->file_path) }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-500">{{ $task->submissionAttachment->file_name }}</a>
                    @endif
                @endif
                @if($task->submitted_at)
                    <div class="text-xs text-gray-500 mt-2">
                        Submitted on {{ $task->submitted_at->format('M d, Y H:i') }}
                        @if($task->submissionAttachment?->user)
                            by {{ $task->submissionAttachment->user->name }}
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if($task->review_comment)
            <div class="text-sm text-gray-700 mb-3 p-3 bg-gray-50 rounded-md">
                <span class="font-medium text-gray-900">Reviewer feedback:</span>
                <div class="mt-1 whitespace-pre-line">{{ $task->review_comment }}</div>
            </div>
        @endif

        @if($task->isApproved())
            <div class="text-sm text-emerald-700">
                This task has been approved{{ $task->reviewed_at ? ' on ' . $task->reviewed_at->format('M d, Y H:i') : '' }} and is complete.
            </div>
        @endif

        @if($task->review_decision === \App\Models\Task::REVIEW_NEEDS_CHANGES)
            <div class="text-sm text-red-700 mb-4">
                Review was not approved — this task has been sent back to the employee for changes.
            </div>
        @endif

        @if($canAdmin && $task->isInReviewCycle())
            <div class="mb-4 p-3 bg-gray-50 rounded-md">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Admin override</div>
                <form method="POST" action="{{ route('reviews.send-back', $task) }}" class="space-y-3">
                    @csrf
                    <div>
                        <x-input-label value="Assign back to (optional)" />
                        <select name="assignee_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                            <option value="">Keep current assignee</option>
                            @foreach($candidates as $candidate)
                                <option value="{{ $candidate->id }}" {{ $task->assignees->first()?->id === $candidate->id ? 'selected' : '' }}>{{ $candidate->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Updates / remarks" />
                        <textarea name="update_message" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" placeholder="Explain the changes the employee needs to make..." required></textarea>
                    </div>
                    <div class="flex justify-end">
                        <x-danger-button type="submit" class="text-xs">Send Back to Employee</x-danger-button>
                    </div>
                </form>
            </div>
        @endif

        @if($task->isAwaitingReview() || $task->isUnderReview())
            @if($isReviewer)
                <form method="POST" action="{{ route('reviews.approve', $task) }}" class="space-y-3">
                    @csrf
                    <div>
                        <x-input-label value="Approval note (optional)" />
                        <textarea name="review_comment" rows="2" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" placeholder="Optional note about your approval..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button type="submit" class="text-xs">Approve Task</x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('reviews.request-changes', $task) }}" class="space-y-3 mt-4 border-t border-gray-200 pt-4">
                    @csrf
                    <div>
                        <x-input-label value="Remarks / changes required" />
                        <textarea name="review_comment" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" placeholder="Describe what needs to change... This will return the task to the employee." required></textarea>
                    </div>
                    <div class="flex justify-end">
                        <x-danger-button type="submit" class="text-xs">Send Back with Remarks</x-danger-button>
                    </div>
                </form>
            @elseif($canManage)
                <div class="text-sm text-gray-700 mb-3">
                    This task has been submitted for review and is
                    {{ $task->hasReviewer() ? 'with ' . $task->reviewer->name . ' for review' : 'awaiting a reviewer' }}.
                </div>

                @if(! $task->hasReviewer() && $candidates->count())
                    <form method="POST" action="{{ route('reviews.assign', $task) }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label value="Assign reviewer" />
                            <select name="reviewer_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="">Select an employee...</option>
                                @foreach($candidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <x-primary-button type="submit" class="text-xs">Assign Reviewer</x-primary-button>
                        </div>
                    </form>
                @elseif($task->hasReviewer() && $candidates->count())
                    <form method="POST" action="{{ route('reviews.assign', $task) }}" class="space-y-3 mt-4 border-t border-gray-200 pt-4">
                        @csrf
                        <div>
                            <x-input-label value="Reassign reviewer" />
                            <select name="reviewer_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                                <option value="">Select another employee...</option>
                                @foreach($candidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <x-secondary-button type="submit" class="text-xs">Change Reviewer</x-secondary-button>
                        </div>
                    </form>
                @endif
@endif
        @endif
    </div>
</div>
@endif