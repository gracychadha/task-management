<?php

namespace App\Enums;

enum TaskStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case UnderReview = 'under_review';
    case ChangesRequested = 'changes_requested';
    case Completed = 'completed';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::UnderReview => 'Under Review',
            self::ChangesRequested => 'Changes Requested',
            self::Completed => 'Completed',
            self::OnHold => 'On Hold',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::InProgress => 'blue',
            self::UnderReview => 'yellow',
            self::ChangesRequested => 'red',
            self::Completed => 'green',
            self::OnHold => 'purple',
            self::Cancelled => 'zinc',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
