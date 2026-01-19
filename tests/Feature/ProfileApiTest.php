<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_success()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson('/api/profile', [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'password' => 'newpassword'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'New Name', 'email' => 'newemail@example.com']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'newemail@example.com'
        ]);
    }

    public function test_profile_update_unauthenticated()
    {
        $response = $this->putJson('/api/profile', ['name' => 'X']);
        $response->assertStatus(401);
    }
}
