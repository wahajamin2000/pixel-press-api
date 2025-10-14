<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Trackable
{
    /**
     * Boot the trackable trait for a model.
     */
    protected static function bootTrackable(): void
    {
        static::creating(function (Model $model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function (Model $model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function (Model $model) {
            if (Auth::check() && $model->isSoftDeleting()) {
                $model->deleted_by = Auth::id();
            }
        });
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    /**
     * Get the user who deleted this record.
     */
    public function deleter()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'deleted_by');
    }

    /**
     * Scope a query to only include records created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include records updated by a specific user.
     */
    public function scopeUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Check if the model uses soft deletes.
     */
    protected function isSoftDeleting(): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(get_class($this)));
    }

    /**
     * Get the name of the user who created this record.
     */
    public function getCreatorNameAttribute(): ?string
    {
        return $this->creator?->name;
    }

    /**
     * Get the name of the user who last updated this record.
     */
    public function getUpdaterNameAttribute(): ?string
    {
        return $this->updater?->name;
    }

    /**
     * Get the name of the user who deleted this record.
     */
    public function getDeleterNameAttribute(): ?string
    {
        return $this->deleter?->name;
    }

    /**
     * Check if the current user created this record.
     */
    public function isCreatedByCurrentUser(): bool
    {
        return Auth::check() && $this->created_by === Auth::id();
    }

    /**
     * Check if the current user last updated this record.
     */
    public function isUpdatedByCurrentUser(): bool
    {
        return Auth::check() && $this->updated_by === Auth::id();
    }

    /**
     * Get tracking information as array.
     */
    public function getTrackingInfo(): array
    {
        return [
            'created_by' => [
                'id' => $this->created_by,
                'name' => $this->creator_name,
                'at' => $this->created_at,
            ],
            'updated_by' => [
                'id' => $this->updated_by,
                'name' => $this->updater_name,
                'at' => $this->updated_at,
            ],
            'deleted_by' => $this->deleted_by ? [
                'id' => $this->deleted_by,
                'name' => $this->deleter_name,
                'at' => $this->deleted_at ?? null,
            ] : null,
        ];
    }
}
