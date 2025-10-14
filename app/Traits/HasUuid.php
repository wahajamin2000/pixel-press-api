<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the HasUuid trait for a model.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the column name for the UUID.
     */
    public function getUuidColumn(): string
    {
        return property_exists($this, 'uuidColumn') ? $this->uuidColumn : 'uuid';
    }

    /**
     * Scope a query to find by UUID.
     */
    public function scopeByUuid($query, string $uuid)
    {
        return $query->where($this->getUuidColumn(), $uuid);
    }

    /**
     * Find a model by its UUID.
     */
    public static function findByUuid(string $uuid): ?self
    {
        return static::byUuid($uuid)->first();
    }

    /**
     * Find a model by its UUID or fail.
     */
    public static function findByUuidOrFail(string $uuid): self
    {
        return static::byUuid($uuid)->firstOrFail();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return $this->getUuidColumn();
    }

    /**
     * Generate a new UUID for the model.
     */
    public function generateUuid(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Regenerate the UUID for the model.
     */
    public function regenerateUuid(): bool
    {
        $this->{$this->getUuidColumn()} = $this->generateUuid();
        return $this->save();
    }
}
