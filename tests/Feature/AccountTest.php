<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/account');

        $response->assertOk();
    }

    public function test_account_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/account', [
                'name'    => 'Test User',
                'phone'   => '0901234567',
                'address' => '123 Test Street',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('0901234567', $user->phone);
        $this->assertSame('123 Test Street', $user->address);
    }

    public function test_unauthenticated_user_cannot_access_account(): void
    {
        $this->get('/account')->assertRedirect('/login');
    }
}
