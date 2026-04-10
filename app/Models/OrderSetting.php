<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class OrderSetting extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
        'is_active',
    ];
}
