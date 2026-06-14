<?php

use App\Enums\UserRole;
use App\Models\User;
use Tests\Helpers\CreatesUsers;

uses(CreatesUsers::class);

describe('Authentication', function () {

    it('allows a user to register', function () {
        $this->post('/register', [
            'name'                  => 'Nguyễn Văn Test',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role'  => UserRole::CUSTOMER->value,
        ]);
    });

    it('new registered user always gets CUSTOMER role', function () {
        // Dù có cố inject role=admin trong request
        $this->post('/register', [
            'name'                  => 'Hacker',
            'email'                 => 'hacker@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'admin', // Cố tình inject
        ]);

        $user = User::where('email', 'hacker@example.com')->first();
        expect($user->role)->toBe(UserRole::CUSTOMER);
    });

    it('does not store plain text password', function () {
        $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'passtest@example.com',
            'password'              => 'mypassword123',
            'password_confirmation' => 'mypassword123',
        ]);

        $user = User::where('email', 'passtest@example.com')->first();
        // Password không được là plain text
        expect($user->password)->not->toBe('mypassword123');
        // Phải hash được verify
        expect(\Illuminate\Support\Facades\Hash::check('mypassword123', $user->password))->toBeTrue();
    });

    it('allows login with correct credentials', function () {
        $user = $this->createCustomer(['email' => 'login@test.com']);

        $this->post('/login', [
            'email'    => 'login@test.com',
            'password' => 'password',
        ])->assertRedirect('/');
    });

    it('rejects login with wrong password', function () {
        $this->createCustomer(['email' => 'login2@test.com']);

        $this->post('/login', [
            'email'    => 'login2@test.com',
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors('email');
    });

    it('redirects unauthenticated user away from account page', function () {
        $this->get('/account')->assertRedirect('/login');
    });

    it('allows authenticated user to access account page', function () {
        $this->actingAsCustomer();
        $this->get('/account')->assertOk();
    });

    it('allows user to logout', function () {
        $this->actingAsCustomer();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    });

});