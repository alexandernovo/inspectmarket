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
            [
                'firstname'   => 'Admin',
                'middlename'  => '',
                'lastname'    => '',
                'username'    => 'admin',
                'designation' => 'Administrator',
                'email'       => 'admin@example.com',
                'address'     => 'Main Office',
                'phone_num'   => '09123456789',
                'status'      => 'ACTIVE',
                'usertype'    => 'ADMIN',
                'password'    => Hash::make('admin'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'firstname'   => 'John',
                'middlename'  => 'D.',
                'lastname'    => 'Doe',
                'username'    => 'treasurer',
                'designation' => 'Treasurer',
                'email'       => 'john@example.com',
                'address'     => 'Branch Office',
                'phone_num'   => '09987654321',
                'status'      => 'ACTIVE',
                'usertype'    => 'TREASURER',
                'password'    => Hash::make('treasurer'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],

            [
                'firstname'   => 'Joana',
                'middlename'  => 'D.',
                'lastname'    => 'Doe',
                'username'    => 'tenant',
                'designation' => 'Tenant',
                'email'       => 'joana@example.com',
                'address'     => 'Branch Office',
                'phone_num'   => '09987654321',
                'status'      => 'ACTIVE',
                'usertype'    => 'TENANT',
                'password'    => Hash::make('tenant'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
