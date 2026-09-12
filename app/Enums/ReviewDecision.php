<?php

namespace App\Enums;

enum ReviewDecision: string
{
    case Approved = 'approved';
    case ChangesRequested = 'changes_requested';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::ChangesRequested => 'Changes Requested',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Approved => 'emerald',
            self::ChangesRequested => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
