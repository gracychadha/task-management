<?php

namespace App\Services;

use App\Enums\ReviewDecision;
use App\Enums\TaskStatus;
use App\Http\Controllers\Concerns\InteractsWithTaskNotifications;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TaskReview;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class TaskWorkflowService
{
    use InteractsWithTaskNotifications;

    public function start(Task $task, User $actor): void
    {
        $this->assertAssignee($task, $actor);
        $this->assertTransition($task, TaskStatus::InProgress, $actor);

        $this->transition($task, TaskStatus::InProgress, $actor, 'started task');
    }

    public function submitForReview(
        Task $task,
        User $actor,
        ?string $submissionLink = null,
        ?UploadedFile $submissionImage = null
    ): void {
        $this->assertAssignee($task, $actor);

        if (! $task->isInProgress() && ! $task->isChangesRequested()) {
            $this->fail('A task can only be submitted for review while it is in progress.');
        }

        $wasChangesRequested = $task->isChangesRequested();

        $updates = [
            'status' => TaskStatus::UnderReview->value,
            'submitted_at' => now(),
            'updates_count' => $task->updates_count + 1,
            'resubmissions_count' => $task->resubmissions_count + ($wasChangesRequested ? 1 : 0),
        ];

        if ($submissionLink) {
            $updates['submission_link'] = $submissionLink;
        }

        if ($submissionImage) {
            $filePath = $submissionImage->store('task-attachments', 'public');
            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'file_path' => $filePath,
                'file_name' => $submissionImage->getClientOriginalName(),
                'file_type' => $submissionImage->getMimeType(),
                'file_size' => $submissionImage->getSize(),
            ]);
            $updates['submission_attachment_id'] = $attachment->id;
        }

        $task->update($updates);

        activity()
            ->performedOn($task)
            ->causedBy($actor)
            ->withProperties(['old' => TaskStatus::InProgress->value, 'new' => TaskStatus::UnderReview->value])
            ->log($wasChangesRequested ? 'resubmitted task for review' : 'submitted task for review');

        if ($task->hasReviewer() && $task->reviewer_id !== $actor->id) {
            $this->notifyUser(
                $task->reviewer->id,
                'task_ready_for_review',
                'Task ready for review',
                "Task {$task->code()} is ready for your review.",
                $task->id
            );
        }

        $this->notifyManagers($task, 'task_ready_for_review', 'Task ready for review',
            "Task {$task->code()} was submitted by {$actor->name} and is awaiting review.", [$actor->id]);
    }

    public function rethink(Task $task, User $actor): void
    {
        $this->assertAssignee($task, $actor);

        if (! $task->isChangesRequested()) {
            $this->fail('Only a task with requested changes can be moved back to in progress.');
        }

        $this->transition($task, TaskStatus::InProgress, $actor, 'started addressing requested changes');
    }

    public function hold(Task $task, User $actor): void
    {
        $this->assertAdministratorOrManager($actor);
        $this->assertTransition($task, TaskStatus::OnHold, $actor);

        $this->transition($task, TaskStatus::OnHold, $actor, 'put task on hold');
    }

    public function release(Task $task, User $actor, string $toStatus): void
    {
        $this->assertAdministratorOrManager($actor);

        if (! in_array($toStatus, [TaskStatus::New->value, TaskStatus::InProgress->value], true)) {
            $this->fail('An on hold task can be released to New or In Progress.');
        }

        $this->assertTransition($task, TaskStatus::from($toStatus), $actor);

        $this->transition($task, TaskStatus::from($toStatus), $actor, 'released task from hold');
    }

    public function cancel(Task $task, User $actor): void
    {
        $this->assertAdministratorOrManager($actor);
        $this->assertTransition($task, TaskStatus::Cancelled, $actor);

        $this->transition($task, TaskStatus::Cancelled, $actor, 'cancelled task');
    }

    public function assignReviewer(Task $task, User $actor, int $reviewerId): void
    {
        $this->assertAdministratorOrManager($actor);

        $task->update([
            'reviewer_id' => $reviewerId,
            'reviewed_at' => null,
            'review_decision' => null,
        ]);

        if ($reviewerId !== $actor->id) {
            $this->notifyUser(
                $reviewerId,
                'task_review_assigned',
                'You have been assigned to review a task',
                "Task {$task->code()} has been assigned to you for review.",
                $task->id
            );
        }

        $this->notifyManagers($task, 'task_review_assigned', 'Reviewer assigned',
            "Task {$task->code()} has been assigned to ".User::findOrFail($reviewerId)->name.' for review.', [$actor->id]);

        activity()
            ->performedOn($task)
            ->causedBy($actor)
            ->withProperties(['reviewer_id' => $reviewerId])
            ->log('assigned '.User::findOrFail($reviewerId)->name.' to review task #'.$task->id);
    }

    public function approve(Task $task, User $actor, ?string $comment = null): void
    {
        $this->assertReviewer($task, $actor);

        if (! $task->isUnderReview()) {
            $this->fail('Only a task under review can be approved.');
        }

        $task->update([
            'status' => TaskStatus::Completed->value,
            'review_decision' => Task::REVIEW_APPROVED,
            'reviewed_at' => now(),
            'review_comment' => $comment,
            'completed_at' => now(),
        ]);

        $this->recordReview($task, $actor, ReviewDecision::Approved, $comment);
        $this->logReview($task, $actor, 'approved');

        $this->notifyAssignees($task, 'task_completed', 'Task completed',
            "Task {$task->code()} has been approved and completed.", [$actor->id]);
        $this->notifyManagers($task, 'task_completed', 'Task completed',
            "Task {$task->code()} has been approved by {$actor->name} and is complete.", [$actor->id]);
        $this->notifyAdmins($task, 'task_completed', 'Task completed',
            "Task {$task->code()} has been approved by {$actor->name} and is complete.", [$actor->id]);
    }

    public function requestChanges(Task $task, User $actor, string $comment, ?int $assigneeId = null): void
    {
        $this->assertReviewer($task, $actor);

        if (! $task->isUnderReview()) {
            $this->fail('Only a task under review can be sent back.');
        }

        $task->update([
            'status' => TaskStatus::ChangesRequested->value,
            'review_decision' => Task::REVIEW_NEEDS_CHANGES,
            'reviewed_at' => now(),
            'review_comment' => $comment,
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'comment' => 'Review feedback: '.$comment,
        ]);

        $this->recordReview($task, $actor, ReviewDecision::ChangesRequested, $comment);
        $this->logReview($task, $actor, 'requested changes');

        $this->notifyAssignees($task, 'task_changes_requested', 'Changes requested',
            "Changes requested for {$task->code()}: {$comment}", [$actor->id]);
        $this->notifyManagers($task, 'task_changes_requested', 'Changes requested',
            "Reviewer requested changes on {$task->code()}: {$comment}", [$actor->id]);
    }

    public function sendBack(Task $task, User $actor, string $message, ?int $assigneeId = null): void
    {
        $this->assertAdministratorOrManager($actor);

        if (! $task->isUnderReview() && ! $task->isCompleted()) {
            $this->fail('Only a submitted or completed task can be sent back.');
        }

        if ($assigneeId) {
            $task->assignees()->sync([$assigneeId]);
        }

        $task->update([
            'status' => TaskStatus::ChangesRequested->value,
            'review_decision' => Task::REVIEW_NEEDS_CHANGES,
            'reviewed_at' => now(),
            'review_comment' => $message,
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'comment' => 'Sent back with updates: '.$message,
        ]);

        $this->recordReview($task, $actor, ReviewDecision::ChangesRequested, $message);

        activity()
            ->performedOn($task)
            ->causedBy($actor)
            ->withProperties(['new' => TaskStatus::ChangesRequested->value])
            ->log("sent task #{$task->id} back for changes");

        $this->notifyAssignees($task, 'task_changes_requested', 'Changes requested',
            "Changes requested for {$task->code()}: {$message}", [$actor->id]);
        $this->notifyManagers($task, 'task_changes_requested', 'Task sent back',
            "Task {$task->code()} was sent back for changes by {$actor->name}: {$message}", [$actor->id]);
    }

    public function assertCanStatusTransition(Task $task, User $actor, string $newStatus): void
    {
        $this->assertTransition($task, TaskStatus::from($newStatus), $actor);
    }

    private function assertTransition(Task $task, TaskStatus $target, User $actor): void
    {
        if ($actor->isEmployee() && ! $this->employeeTransitionAllowed($task, $target)) {
            $this->fail('This status change is not allowed from the current state.');
        }

        if ($actor->isManager() && ! $this->managerTransitionAllowed($task, $target)) {
            $this->fail('This status change is not allowed from the current state.');
        }
    }

    private function employeeTransitionAllowed(Task $task, TaskStatus $target): bool
    {
        return match ($target) {
            TaskStatus::New => false,
            TaskStatus::InProgress => $task->isNew() || $task->isChangesRequested() || $task->isOnHold(),
            TaskStatus::UnderReview => $task->isInProgress() || $task->isChangesRequested(),
            TaskStatus::Completed, TaskStatus::OnHold, TaskStatus::Cancelled => false,
            TaskStatus::ChangesRequested => false,
        };
    }

    private function managerTransitionAllowed(Task $task, TaskStatus $target): bool
    {
        return match ($target) {
            TaskStatus::New => $task->isOnHold(),
            TaskStatus::InProgress => $task->isNew() || $task->isOnHold() || $task->isChangesRequested(),
            TaskStatus::UnderReview => true,
            TaskStatus::OnHold => $task->isActive(),
            TaskStatus::Cancelled => $task->isActive(),
            TaskStatus::Completed => $task->isUnderReview() && $task->review_decision === Task::REVIEW_APPROVED,
            TaskStatus::ChangesRequested => false,
        };
    }

    private function transition(Task $task, TaskStatus $to, User $actor, string $log): void
    {
        $old = $task->status;

        $task->update(['status' => $to->value]);

        activity()
            ->performedOn($task)
            ->causedBy($actor)
            ->withProperties(['old' => $old, 'new' => $to->value])
            ->log($log);

        $this->notifyManagers($task, 'task_status', 'Task status changed',
            "Task {$task->code()} moved to ".$to->label()." by {$actor->name}.", [$actor->id]);
    }

    private function recordReview(Task $task, User $actor, ReviewDecision $decision, ?string $comment): void
    {
        TaskReview::create([
            'task_id' => $task->id,
            'reviewer_id' => $actor->id,
            'review_number' => $task->reviews()->count() + 1,
            'decision' => $decision->value,
            'comment' => $comment,
            'reviewed_at' => now(),
        ]);
    }

    private function logReview(Task $task, User $actor, string $action): void
    {
        activity()
            ->performedOn($task)
            ->causedBy($actor)
            ->withProperties(['new' => $task->status])
            ->log("{$action} task #{$task->id}");
    }

    private function assertAssignee(Task $task, User $actor): void
    {
        if (! $task->assignees->contains('id', $actor->id)) {
            abort(403, 'You are not assigned to this task.');
        }
    }

    private function assertReviewer(Task $task, User $actor): void
    {
        if (! $actor->isAdmin() && $task->reviewer_id !== $actor->id) {
            abort(403, 'You are not the assigned reviewer for this task.');
        }
    }

    private function assertAdministratorOrManager(User $actor): void
    {
        if (! $actor->isAdmin() && ! $actor->isManager()) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }
}
