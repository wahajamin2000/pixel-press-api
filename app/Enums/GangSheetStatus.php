<?php

namespace App\Enums;

enum GangSheetStatus: string
{
    case DRAFT = 'draft';
    case FINALIZED = 'finalized';
    case PRINTED = 'printed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::FINALIZED => 'Finalized',
            self::PRINTED => 'Printed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'yellow',
            self::FINALIZED => 'blue',
            self::PRINTED => 'green',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::FINALIZED, self::PRINTED]);
    }
}
