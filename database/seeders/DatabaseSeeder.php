<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'firstname'   => 'Admin',
            'middlename'  => '',
            'lastname'    => '',
            'username'    => 'admin',
            'designation' => 'Administrator',
            'email'       => 'admin@example.com',
            'address'     => 'Main Office',
            'phone_num'   => '09123456789',
            'status'      => 'ACTIVE',
            'usertype'      => 'ADMIN',
            'password'    => Hash::make('admin'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
