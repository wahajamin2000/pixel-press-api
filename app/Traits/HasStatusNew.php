<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatusNew
{
    /**
     * Scope a query to only include active records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive records.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the record is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status?->value === 'active';
    }

    /**
     * Check if the record is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive' || $this->status?->value === 'inactive';
    }

    /**
     * Activate the record.
     */
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Deactivate the record.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => 'inactive']);
    }

    /**
     * Toggle the status of the record.
     */
    public function toggleStatus(): bool
    {
        $newStatus = $this->isActive() ? 'inactive' : 'active';
        return $this->update(['status' => $newStatus]);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(): string
    {
        if (is_object($this->status) && method_exists($this->status, 'label')) {
            return $this->status->label();
        }

        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the status color.
     */
    public function getStatusColor(): string
    {
        if (is_object($this->status) && method_exists($this->status, 'color')) {
            return $this->status->color();
        }

        return match($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            'failed' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if status can transition to another status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if (is_object($this->status) && method_exists($this->status, 'canTransitionTo')) {
            // For enum-based statuses
            $enumClass = get_class($this->status);
            $newStatusEnum = $enumClass::from($newStatus);
            return $this->status->canTransitionTo($newStatusEnum);
        }

        // Default transition rules for string-based statuses
        return match($this->status) {
            'draft' => in_array($newStatus, ['active', 'inactive']),
            'active' => in_array($newStatus, ['inactive']),
            'inactive' => in_array($newStatus, ['active']),
            'pending' => in_array($newStatus, ['processing', 'cancelled']),
            'processing' => in_array($newStatus, ['completed', 'cancelled']),
            'completed' => false,
            'cancelled' => false,
            default => true,
        };
    }

    /**
     * Update status with validation.
     */
    public function updateStatus(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status} to {$newStatus}"
            );
        }

        return $this->update(['status' => $newStatus]);
    }
}
