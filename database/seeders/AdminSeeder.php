<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     $exist =  User::SuperAdmin()->first();
        if(!$exist){
            $first_name = 'Super';
            $last_name = 'Admin';
            $name = $first_name .' '.$last_name;
            $email = 'superadmin@pixelpress.com';

            User::create([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'slug' => generate_slug($name,32,'user-'),
                'email' => $email,
                'password' => Hash::make('sup3rAdm!n#P!xel'),
                'role' => User::ROLES[User::LEVEL_SUPER_ADMIN],
                'level' => User::LEVEL_SUPER_ADMIN,
                'status' => StatusEnum::Active->value,
                'pic' => get_gravatar($email),
                'thumb' => get_gravatar($email),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}
