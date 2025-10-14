<?php

namespace App\Enums;

enum StatusEnum: int
{
    case Blocked = 0;
    case Active  = 1;

    public function label(): string
    {
        return match ($this) {
            self::Active  => 'Active',
            self::Blocked => 'Blocked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active  => 'success',
            self::Blocked => 'danger',
        };
    }

    public static function options(): array
    {
        return [
            self::Active->value  => self::Active->label(),
            self::Blocked->value => self::Blocked->label(),
        ];
    }
}
