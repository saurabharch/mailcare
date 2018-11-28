<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Email;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DeleteEmailTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_can_soft_delete_email()
    {
        $email = factory(Email::class)->create();

        $this->assertCount(1, Email::all());

        $response = $this->json('DELETE', 'api/emails/'.$email->id);

        $response->assertStatus(200);
        $this->assertCount(0, Email::all());
        $this->assertCount(1, Email::withTrashed()->get());
    }
}
