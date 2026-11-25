<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'role' => 1,
            'email' => 'missastaneh@gmail.com',
            'password' => '123Ss!45678',
            'password_confirmation' => '123Ss!45678',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'missastaneh@gmail.com',
        ]);
    }
}