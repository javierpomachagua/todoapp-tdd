<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_user_can_has_own_goals()
    {
        // User login
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $this->assertAuthenticated('api');

        // Create goal for that user
        $goal = $user->goals()->create([
            'name' => 'Lose Weight'            
        ]);

        // Show goals of user
        $goals = $user->goals()->count();

        // Compare quantity
        $this->assertEquals($goals, 1);
    }

    public function test_an_user_cannot_has_other_goals()
    {
        // Create user and create goals for that user
        $firstUser = User::factory()->create();

        $firstUser->goals()->create([
            'name' => 'Lose Weight'
        ]);

        // Login with other user and show goals
        $secondUser = User::factory()->create();        

        $secondUserGoals = $secondUser->goals()->count();

        // Assert that second user cannot see first user goals
        $this->assertEquals($secondUserGoals, 0);
    }

    public function test_an_user_can_save_goals()
    {
        $this->withoutExceptionHandling();
        
        // Login as user
        $user = User::factory()->create();

        $this->actingAs($user);

        // Create a goal for that user requesting an api
        $response = $this->post('api/goals', [
            'name' => 'Lose Weight',
            'user_id' => $user->id
        ]);

        // Return 201
        // Return message
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Goal created!'
            ]);

    }

    
}
