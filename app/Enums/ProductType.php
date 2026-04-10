<?php

namespace App\Enums;

enum ProductType: string
{
    case BASIC = 'basic';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::BASIC => 'Basic',
            self::CUSTOM => 'Custom',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BASIC => 'green',
            self::CUSTOM => 'danger',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isBasic(): bool
    {
        return $this === self::BASIC;
    }

    public function isCustom(): bool
    {
        return $this === self::CUSTOM;
    }

}
