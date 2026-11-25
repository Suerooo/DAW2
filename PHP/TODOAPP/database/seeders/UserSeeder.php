<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash as FacadesHash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = new User();
        $user->name = "Admin";
        $user->email = "admin@todo.com";
        $user->password = FacadesHash::make("password");
        $user->save();
    }
}
