<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::firstOrCreate(
            ['email' => 'admin@giftshop.test'],
            [
                'name'     => 'Admin Gift Shop',
                'email'    => 'admin@giftshop.test',
                'password' => 'password123',   // Cast 'hashed' tự bcrypt
                'role'     => UserRole::ADMIN,
            ]
        );

        // Demo customer account
        User::firstOrCreate(
            ['email' => 'customer@giftshop.test'],
            [
                'name'     => 'Nguyễn Văn A',
                'email'    => 'customer@giftshop.test',
                'password' => 'password123',
                'role'     => UserRole::CUSTOMER,
            ]
        );

        $this->command->info('✅ Users seeded!');
        $this->command->info('   Admin:    admin@giftshop.test / password123');
        $this->command->info('   Customer: customer@giftshop.test / password123');
    }
}
