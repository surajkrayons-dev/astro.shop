<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateDefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('users')->insert([
            'type' => 'admin',
            'role_id' => \DB::table('roles')->first()->id,
            'username' => 'super_admin',
            'name' => 'Super Admin',
            'email' => 'super-admin@demo.com',
            'password' => bcrypt('111111'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
