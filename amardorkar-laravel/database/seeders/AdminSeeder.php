<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Default super admin — matches PHP original credentials
        Admin::firstOrCreate(
            ['email' => 'admin@doelhosting.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role'     => 'super_admin',
            ]
        );
    }
}
