<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ProductCategory extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasStatus, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status' => StatusEnum::class,
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function products(): HasMany
    {
        return $this->hasMany(Product::class,'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    // Accessors
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

}
