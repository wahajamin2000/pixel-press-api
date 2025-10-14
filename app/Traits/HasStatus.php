<?php

namespace App\Traits;

use App\Enums\StatusEnum;

trait HasStatus
{

    /*
    |--------------------------------------------------------------------------
    | Scope Methods
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', StatusEnum::Active->value);
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', StatusEnum::Blocked->value);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    */

    public function getStatusNameAttribute(): string
    {
        return StatusEnum::tryFrom($this->status->value)?->label() ?? '';
    }

    public function statusColor(): string
    {
        return StatusEnum::tryFrom($this->status->value)?->color() ?? '';
    }

    public function isActive(): bool
    {
        return $this->status === StatusEnum::Active->value;
    }

    public function isBlocked(): bool
    {
        return $this->status === StatusEnum::Blocked->value;
    }

}
