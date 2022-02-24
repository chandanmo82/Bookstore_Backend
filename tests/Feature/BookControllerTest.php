<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    /**
     * @test for
     * Admin Book Addition successfull
     */
    
    public function test_SuccessfulAddBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/addbook', [
            "name" => "user",
            "description" => "deepak",
            "author" => "kumar",
            "image" => "kyc@gmail.com",
            "Price"=> "9685741236",
            "quantity" => "deepak@123",
            "confirm_password" => "deepak@123"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'User successfully registered']);
    }

    /**
     * @test for
     * Admin Book Quantity Addition successfull
     */
    
    public function test_SuccessfullAddQuantityToExistingBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
        ])->json('POST', '/api/auth/addquantity', 
        [
            "id" => "3",
            "quantity" => "10"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Quantity updated Successfully']);
    }

    /**
     * @test for
     * Admin Book Quantity Addition Unsuccessfull
     */
    public function test_UnSuccessfullAddQuantityToExistingBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
        ])->json('POST', '/api/auth/addquantity', 
        [
            "id" => "15",
            "quantity" => "10"
        ]);
        $response->assertStatus(404)->assertJson(['message' => 'Couldnot found a book with that given id']);
    }

    /**
     * @test for
     * Admin Delete Book successfull
     */
    public function test_SuccessfullDeleteBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
        ])->json('POST', '/api/auth/addquantity', 
        [
            "id" => "3",
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book deleted Sucessfully']);
    }

    /**
     * @test for
     * Admin Delete Book Unsuccessfull
     */
    public function test_UnSuccessfullDeleteBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
        ])->json('POST', '/api/auth/addquantity', 
        [
            "id" => "45",
        ]);
        $response->assertStatus(404)->assertJson(['message' => 'Book not Found']);
    }

}
