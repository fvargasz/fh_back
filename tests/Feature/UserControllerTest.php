<?php

namespace Tests\Feature;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function create_user_route_returns_name()
    {
        // Send POST request to /users/create
        $response = $this->post('/users/create', [
            'name' => 'John Doe',
            'password' => 'a'
        ]);

        // Assert the response status is 201 (Created)
        $response->assertStatus(201);
        
        // Assert the response JSON structure and content
        $response->assertJson([
            'message' => 'User created successfully',
            'user' => [
                'name' => 'John Doe',
                'email' => 'default@example.com'
            ]
        ]);

        // Or just check if it has the expected structure
        $response->assertJsonStructure([
            'message',
            'user' => ['name', 'email']
        ]);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'default@example.com'
        ]);
    }

    /** @test */
    public function create_user_requires_name_field()
    {
        // Send POST request without name field
        $response = $this->post('/users/create');

        // Assert bad request status
        $response->assertStatus(400);
        
        // Assert error message
        $response->assertJson([
            'error' => 'name field is required'
        ]);
    }

    /** @test */
    public function create_user_requires_non_empty_name()
    {
        // Send POST request with empty name
        $response = $this->post('/users/create', [
            'name' => ''
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'name field is required'
        ]);
    }
}
