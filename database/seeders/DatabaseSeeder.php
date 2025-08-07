<?php

namespace Database\Seeders;

use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Panggil RoleSeeder terlebih dahulu untuk memastikan peran sudah ada
        $this->call(RoleSeeder::class);

        // Ambil ID dari setiap role SETELAH dibuat
        $adminRole = Role::where('name', 'admin')->first();

                // 1. Membuat User Admin dan menetapkan perannya
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->roles()->attach($adminRole->id);

        
        // 5. Panggil seeder lain
        $this->call([
            RoleSeeder::class
        ]);
    }
}
