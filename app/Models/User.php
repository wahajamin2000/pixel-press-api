<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Interfaces\ColorsCodeInterface;
use App\Traits\HasStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements ColorsCodeInterface
{
    use HasApiTokens, HasFactory, Notifiable, HasStatus, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status' => StatusEnum::class,
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
 |--------------------------------------------------------------------------
 | Override Methods
 |--------------------------------------------------------------------------
 */

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Const Variables
    |--------------------------------------------------------------------------
    */

    /* ================= Level ======================== */
    public const COLOR_SUPER_ADMIN = self::CLASS_SUCCESS;
    public const COLOR_ADMIN       = self::CLASS_SECONDARY;
    public const COLOR_MANAGER     = self::CLASS_DANGER;
    public const COLOR_CUSTOMER      = self::CLASS_WARNING;

    public const KEY_LEVEL_SUPER_ADMIN  = 'super_admin';
    public const KEY_LEVEL_ADMIN        = 'admin';
    public const KEY_LEVEL_MANAGER      = 'manager';
    public const KEY_LEVEL_CUSTOMER       = 'customer';

    public const LEVEL_SUPER_ADMIN      = 1001;
    public const LEVEL_ADMIN            = 3001;
    public const LEVEL_MANAGER          = 5001;
    public const LEVEL_CUSTOMER         = 7001;

    public const LEVELS = [
        self::LEVEL_SUPER_ADMIN => self::KEY_LEVEL_SUPER_ADMIN,
        self::LEVEL_ADMIN       => self::KEY_LEVEL_ADMIN,
        self::LEVEL_MANAGER     => self::KEY_LEVEL_MANAGER,
        self::LEVEL_CUSTOMER      => self::KEY_LEVEL_CUSTOMER,
    ];

    public const LEVEL_COLORS = [
        self::KEY_LEVEL_SUPER_ADMIN => self::COLOR_SUPER_ADMIN,
        self::KEY_LEVEL_ADMIN       => self::COLOR_ADMIN,
        self::KEY_LEVEL_MANAGER     => self::COLOR_MANAGER,
        self::KEY_LEVEL_CUSTOMER      => self::COLOR_CUSTOMER,
    ];

    /* ================= Role ======================== */
    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_ADMIN       = 'Admin';
    public const ROLE_MANAGER     = 'Manager';
    public const ROLE_CUSTOMER      = 'Customer';

    public const ROLES = [
        self::LEVEL_SUPER_ADMIN => self::ROLE_SUPER_ADMIN,
        self::LEVEL_ADMIN       => self::ROLE_ADMIN,
        self::LEVEL_MANAGER     => self::ROLE_MANAGER,
        self::LEVEL_CUSTOMER      => self::ROLE_CUSTOMER,
    ];

    public const ROLE_COLORS = [
        self::LEVEL_SUPER_ADMIN => self::COLOR_SUPER_ADMIN,
        self::LEVEL_ADMIN       => self::COLOR_ADMIN,
        self::LEVEL_MANAGER     => self::COLOR_MANAGER,
        self::LEVEL_CUSTOMER      => self::COLOR_CUSTOMER,
    ];

    const USER_ROLES = [
        self::LEVEL_ADMIN   => 'Admin',
        self::LEVEL_MANAGER => 'Manager',
    ];

    public function getRoles()
    {
        $roles = [];
        foreach (self::USER_ROLES as $index => $role) {
            $roles[$index] = $role;
        }

        return $roles;
    }


    /* ================= Gender ======================== */
    public const COLOR_GENDER_MALE   = self::PRIMARY;
    public const COLOR_GENDER_FEMALE = self::DANGER;

    public const KEY_GENDER_MALE   = 0;
    public const KEY_GENDER_FEMALE = 1;

    public const GENDER_MALE   = 'Male';
    public const GENDER_FEMALE = 'Female';

    public const GENDERS       = [
        self::KEY_GENDER_MALE   => self::GENDER_MALE,
        self::KEY_GENDER_FEMALE => self::GENDER_FEMALE,
    ];

    public const GENDER_COLORS = [
        self::KEY_GENDER_MALE   => self::COLOR_GENDER_MALE,
        self::KEY_GENDER_FEMALE => self::COLOR_GENDER_FEMALE,
    ];

    /*
    |--------------------------------------------------------------------------
    | Scope Methods
    |--------------------------------------------------------------------------
    */

    /* ================= Scope Level ======================== */
    public function scopeSuperAdmin($query)
    {
        return $query->where('level', '=', self::LEVEL_SUPER_ADMIN);
    }

    public function scopeAdmins($query)
    {
        return $query->where('level', '=', self::LEVEL_ADMIN);
    }

    public function scopeManagers($query)
    {
        return $query->where('level', '=', self::LEVEL_MANAGER);
    }

    public function scopeCustomers($query)
    {
        return $query->where('level', '=', self::LEVEL_CUSTOMER);
    }

    public function scopeUsers($query)
    {
        return $query->whereIn('level', [self::LEVEL_ADMIN,self::LEVEL_MANAGER]);
    }


    /* ================= Scope Search ======================== */
    public function scopeSearch($query, $data = [])
    {
        if (isset($data['daterange']) && !empty(isset($data['daterange']))) {
            $dateRange = explode(' - ', $data['daterange']);
            $query     = $query->whereBetween('created_at', $dateRange);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Validations
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }


    /**
     * Generate temporary password
     */
    public static function generateTemporaryPassword(): string
    {
        return Str::random(12);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */


    public function getNameAttribute()
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }

    public function getAddressAttribute()
    {
        $parts = [
            $this->address_line_one,
            $this->address_line_two,
            $this->city,
            $this->state,
            $this->post_code,
        ];

        $parts = array_filter($parts);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    public function getRoleNameAttribute()
    {
        return self::ROLES[$this->attributes['level']];
    }

    public function getGenderNameAttribute()
    {
        if (!isset($this->attributes['gender'])) return null;
        return self::GENDERS[$this->attributes['gender']] ?? '';
    }

    public function getPicAttribute(): ?string
    {
        if (empty($this->attributes['pic'])) {
            return asset('images/avatar.png');
        }

        return $this->attributes['pic'];
    }

    public function getTotalSpentAttribute(): float
    {
        return $this->orders()
            ->whereIn('status', ['payment_confirmed', 'processing', 'printing', 'shipped', 'delivered'])
            ->sum('total_amount');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    */

    /* Fetching Helpers */
    public static function fetchRecord($value, $by = 'slug')
    {
        return self::where($by, '=', $value)->first();
    }

    public static function fetchRecordId($value, $by = 'slug')
    {
        return self::where($by, '=', $value)->pluck('id')->first();
    }

    public static function recordExists($value, $by = 'slug')
    {
        return self::where($by, '=', $value)->exists();
    }

    public static function incrementColumnBy($recordId, $column = 'usedBy', $amount = 1)
    {
        return self::where('id', '=', $recordId)->increment($column, $amount);
    }

    public static function decrementColumnBy($recordId, $column = 'usedBy', $amount = 1)
    {
        return self::where('id', '=', $recordId)->decrement($column, $amount);
    }

    /* Color Helpers */
    public function levelColor()
    {
        return self::LEVEL_COLORS[$this->level];
    }

    public function roleColor()
    {
        if (!isset($this->roleName)) return '';
        return self::ROLE_COLORS[$this->level];
    }

    /* Conditional Helpers */
    public function isLevel($level)
    {
        return $this->level == $level;
    }

    public function isRole($role)
    {
        return $this->role == $role;
    }

    public function isSuperAdmin()
    {
        return $this->level === self::LEVEL_SUPER_ADMIN;
    }

    public function isAdmin()
    {
        return $this->level === self::LEVEL_ADMIN;
    }

    public function isManager()
    {
        return $this->level === self::LEVEL_MANAGER;
    }

    public function isCustomer()
    {
        return $this->level === self::LEVEL_CUSTOMER;
    }

    public function deletable()
    {
        return is_level(User::LEVEL_SUPER_ADMIN,auth()->user()->id) == true;
    }

    public function restorable()
    {
        return true;
    }

    public function forceDeletable()
    {
        return true;
    }

    public function deleteInstance()
    {
        $this->delete();
    }

    /* Other Helpers */
    public static function prepareSearchData(\Illuminate\Http\Request $request)
    {
        return $data = [];
    }
    /* Update Path Helpers */


    /**
     * Create customer with temporary password
     */
    public static function createWithTemporaryPassword(array $data): self
    {
        $temporaryPassword = self::generateTemporaryPassword();
        $name = $data['first_name'] .' '. $data['last_name'];

        $customer = self::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'slug' => generate_slug($name,32,'user-'),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($temporaryPassword),
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'role'             => User::ROLES[User::LEVEL_CUSTOMER],
            'level'            => User::LEVEL_CUSTOMER,
            'status'           => StatusEnum::Active->value,
        ]);

        // Store temporary password for email sending
        $customer->temporary_password = $temporaryPassword;

        return $customer;
    }


    /**
     * Check if customer has verified email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): void
    {
        $this->email_verified_at = now();
        $this->save();
    }


    /*
   |--------------------------------------------------------------------------
   | Functionality Functions
   |--------------------------------------------------------------------------
   */

//    public function savePicture($image = null)
//    {
//        $disk = Media::KEY_DISK_USER;
//
//        if (isset($this->picture)) $oldPic = $this->picture->replicate();
//
//        $data = MediaController::StoreMedia($image, 'shared', $disk);
//        if (!(isset($data) && $data['result'])) return false;
//
//        $image = isset($oldPic) ? MediaController::update($data, $this->picture, $disk, Media::KEY_DISK_USER) : MediaController::save($data, $this, $disk, Media::KEY_DISK_USER);
//
//        $this->pic = $image->thumb_url;
//        $this->save();
//
//        if (isset($oldPic)) {
//            MediaController::DeleteMedia('shared', $disk, $oldPic->media_name);
//            MediaController::DeleteMedia('shared', $disk, $oldPic->thumb_name);
//        }
//
//        return $image->media_url;
//    }


    /*
   |--------------------------------------------------------------------------
   | Validations
   |--------------------------------------------------------------------------
   */


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get customer orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class,'deleted_by');
    }

}

