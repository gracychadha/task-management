<?php

namespace App\Models;

use App\Enums\ReviewDecision;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'department_id', 'status', 'priority', 'due_date', 'start_date', 'created_by', 'parent_id', 'estimated_hours', 'actual_hours', 'sort_order', 'reviewer_id', 'reviewed_at', 'review_decision', 'review_comment', 'submission_link', 'submitted_at', 'submission_attachment_id', 'updates_count', 'resubmissions_count', 'completed_at'])]
class Task extends Model
{
    use HasFactory;

    public const REVIEW_APPROVED = 'approved';

    public const REVIEW_NEEDS_CHANGES = 'changes_requested';

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'start_date' => 'date',
            'created_by' => 'integer',
            'parent_id' => 'integer',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'sort_order' => 'integer',
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'updates_count' => 'integer',
            'resubmissions_count' => 'integer',
        ];
    }

    public function code(): string
    {
        return 'TASK-'.$this->id;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TaskReview::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function submissionAttachment(): BelongsTo
    {
        return $this->belongsTo(TaskAttachment::class, 'submission_attachment_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'task_label');
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->whereHas('assignees', fn ($q) => $q->where('users.id', $userId));
    }

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId)
            ->orWhereHas('assignees', fn ($q) => $q->where('users.department_id', $departmentId));
    }

    public function scopeForReviewer(Builder $query, int $userId): Builder
    {
        return $query->where('reviewer_id', $userId);
    }

    public function scopePendingReviewFor(Builder $query, int $userId): Builder
    {
        return $query->where('reviewer_id', $userId)
            ->where('status', TaskStatus::UnderReview->value);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            $deptId = $user->department_id;

            return $query->where(function ($q) use ($user, $deptId) {
                $q->whereHas('assignees', function ($q2) use ($deptId, $user) {
                    $q2->where('users.id', $user->id);
                    if ($deptId) {
                        $q2->orWhere('users.department_id', $deptId);
                    }
                })->orWhere('created_by', $user->id);
            });
        }

        return $query->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->orWhere('created_by', $user->id)
            ->orWhere('reviewer_id', $user->id);
    }

    public function getStatusLabel(): string
    {
        return TaskStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return TaskStatus::tryFrom($this->status)?->color() ?? 'gray';
    }

    public function getPriorityLabel(): string
    {
        return TaskPriority::tryFrom($this->priority)?->label() ?? $this->priority;
    }

    public function getPriorityColor(): string
    {
        return TaskPriority::tryFrom($this->priority)?->color() ?? 'gray';
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Completed->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === TaskStatus::Cancelled->value;
    }

    public function isUnderReview(): bool
    {
        return $this->status === TaskStatus::UnderReview->value;
    }

    public function isChangesRequested(): bool
    {
        return $this->status === TaskStatus::ChangesRequested->value;
    }

    public function isInProgress(): bool
    {
        return $this->status === TaskStatus::InProgress->value;
    }

    public function isNew(): bool
    {
        return $this->status === TaskStatus::New->value;
    }

    public function isOnHold(): bool
    {
        return $this->status === TaskStatus::OnHold->value;
    }

    public function isAwaitingReview(): bool
    {
        return $this->isUnderReview() && $this->reviewer_id === null;
    }

    public function isUnderReviewByReviewer(): bool
    {
        return $this->isUnderReview() && $this->reviewer_id !== null;
    }

    public function isApproved(): bool
    {
        return $this->isCompleted() && $this->review_decision === self::REVIEW_APPROVED;
    }

    public function hasReviewer(): bool
    {
        return $this->reviewer_id !== null;
    }

    public function hasSubmission(): bool
    {
        return $this->submitted_at !== null
            || $this->submission_link !== null
            || $this->submission_attachment_id !== null;
    }

    public function isInReviewCycle(): bool
    {
        return in_array($this->status, [
            TaskStatus::UnderReview->value,
            TaskStatus::ChangesRequested->value,
        ]) || $this->hasReviewer();
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    public function isEditableByAssignedEmployee(): bool
    {
        return in_array($this->status, [
            TaskStatus::New->value,
            TaskStatus::InProgress->value,
            TaskStatus::ChangesRequested->value,
        ]);
    }

    public function reviewCyclesCount(): int
    {
        return $this->reviews()->count();
    }

    public function changesRequestedCount(): int
    {
        return $this->reviews()
            ->where('decision', ReviewDecision::ChangesRequested->value)
            ->count();
    }

    public function resubmissions(): int
    {
        return max(0, $this->updates_count - 1);
    }

    public function getReviewStatusLabel(): string
    {
        if ($this->isCancelled()) {
            return 'Cancelled';
        }

        if ($this->isCompleted()) {
            return 'Approved & Complete';
        }

        if ($this->isChangesRequested()) {
            return 'Changes Requested';
        }

        if ($this->isUnderReviewByReviewer()) {
            return 'Under Review';
        }

        if ($this->isUnderReview()) {
            return 'Awaiting Reviewer';
        }

        return 'Not In Review';
    }

    public function getReviewStatusColor(): string
    {
        if ($this->isCancelled()) {
            return 'bg-zinc-100 text-zinc-700';
        }

        if ($this->isCompleted()) {
            return 'bg-emerald-100 text-emerald-800';
        }

        if ($this->isChangesRequested()) {
            return 'bg-red-100 text-red-800';
        }

        if ($this->isUnderReviewByReviewer()) {
            return 'bg-yellow-100 text-yellow-800';
        }

        if ($this->isUnderReview()) {
            return 'bg-indigo-100 text-indigo-800';
        }

        return 'bg-gray-100 text-gray-700';
    }
}
