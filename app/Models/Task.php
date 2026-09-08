<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'status', 'priority', 'due_date', 'start_date', 'created_by', 'parent_id', 'estimated_hours', 'actual_hours', 'sort_order'])]
class Task extends Model
{
    use HasFactory;

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
        ];
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
        return $query->whereHas('assignees', fn ($q) => $q->where('users.department_id', $departmentId));
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Done->value]);
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
            ->orWhere('created_by', $user->id);
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
        return $this->due_date && $this->due_date->isPast() && $this->status !== TaskStatus::Done->value;
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Done->value;
    }
}
