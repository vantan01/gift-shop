<?php

namespace Tests\Helpers;

use App\Enums\UserRole;
use App\Models\User;

trait CreatesUsers
{
    protected function createCustomer(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::CUSTOMER,
        ], $overrides));
    }

    protected function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::ADMIN,
        ], $overrides));
    }

    protected function actingAsCustomer(array $overrides = []): User
    {
        $user = $this->createCustomer($overrides);
        $this->actingAs($user);
        return $user;
    }

    protected function actingAsAdmin(array $overrides = []): User
    {
        $user = $this->createAdmin($overrides);
        $this->actingAs($user);
        return $user;
    }
}