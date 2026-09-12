<?php

namespace App\Models;

use App\Enums\ReviewDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['task_id', 'reviewer_id', 'review_number', 'decision', 'comment', 'reviewed_at'])]
class TaskReview extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'review_number' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function decisionLabel(): string
    {
        return ReviewDecision::tryFrom($this->decision)?->label() ?? $this->decision;
    }

    public function decisionColor(): string
    {
        return ReviewDecision::tryFrom($this->decision)?->color() ?? 'gray';
    }
}
