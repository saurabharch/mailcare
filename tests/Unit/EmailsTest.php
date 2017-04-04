<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use \Carbon\Carbon;

class EmailsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_fetches_latest_emails()
    {
    	$emailOne = factory(\App\Email::class)->create([
    		'subject' => 'My first email',
    		'created_at' => Carbon::yesterday()
    		]);
    	$emailTwo = factory(\App\Email::class)->create([
    		'subject' => 'My second email',
    		'created_at' => Carbon::now()
    		]);

    	$response = $this->json('GET', 'api/v1/emails');

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => $emailOne->subject])
            ->assertJsonFragment(['subject' => $emailTwo->subject]);

    	$data = json_decode($response->baseResponse->content())->data;
        $this->assertEquals($emailTwo->subject, $data[0]->subject);
        $this->assertEquals($emailOne->subject, $data[1]->subject);
    }

    /**
     * @test
     */
    public function it_fetches_a_single_email()
    {
    	$email = factory(\App\Email::class)->create();

    	$response = $this->json('GET', 'api/v1/emails/'.$email->id);

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
