<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EmailsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_fetches_emails()
    {
    	$email = factory(\App\Email::class)->create();

    	$response = $this->json('GET', 'api/v1/emails');

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => $email->subject]);
    }

    /**
     * @test
     */
    public function it_fetches_a_single_email()
    {
    	$email = factory(\App\Email::class)->create();

    	$response = $this->json('GET', 'api/v1/emails/'.$email->id)->data;

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => $email->subject]);
    }

    /**
     * @test
     */
    public function it_fetches_an_email_that_doesnt_exist()
    {

    	$response = $this->json('GET', 'api/v1/emails/id-doesnt-exist');

        $response
            ->assertStatus(404);
    }
}
