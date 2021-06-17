<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->assertAuthenticated();
    }

    public function test_an_user_can_has_own_goals()
    {
        $goal = $this->user->goals()->create([
            'name' => 'Lose Weight'            
        ]);

        // Show goals of user
        $goals = $this->user->goals()->count();

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

        $response = $this->post('api/goals', [
            'name' => 'Lose Weight',
            'user_id' => $this->user->id
        ]);

        $this->assertEquals(1, Goal::all()->count());

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Goal created!'
            ]);

    }

    public function test_a_goal_requires_a_name()
    {
        $goal = Goal::factory()->make([
            'name' => null
        ]);

        $response = $this->post('api/goals', $goal->toArray());

        $response->assertSessionHasErrors('name');
    }

    public function test_a_goal_requires_a_user()
    {
        $goal = Goal::factory()->make([
            'user_id' => null
        ]);

        $response = $this->post('api/goals', $goal->toArray());

        $response->assertSessionHasErrors('user_id');
    }

    public function test_an_user_can_update_own_goal()
    {
        $this->withoutExceptionHandling();

        // Create a goal that belongs to the user authenticated
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id
        ]);

        $goal->name = 'Goal with new name';

        // Can update an own goal
        $this->put("api/goals/{$goal->id}", $goal->toArray());

        // The goal should be updated successfully
        $this->assertDatabaseHas('goals', $goal->toArray());
    }

    public function test_an_user_cannot_update_unowned_goal()
    {
        $this->withoutExceptionHandling();

        // Create a goal that belongs to another user
        $goal = Goal::factory()->create();

        // dd($this->user->id, $goal->user_id, auth()->user()->id);
        $this->expectException(AuthorizationException::class);

        // Update goal
        $this->put("api/goals/{$goal->id}", $goal->toArray());

        // Return forbidden error
        // $response->assertStatus(201);
    }

    
}
