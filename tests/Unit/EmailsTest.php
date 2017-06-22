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
    public function it_fetches_limited_emails_per_default()
    {
    	define('MAX_LIMIT', 25);
    	$emails = factory(\App\Email::class, 100)->create();

    	$response = $this->json('GET', 'api/v1/emails');

        $response->assertStatus(200)->assertJsonFragment(['paginator' => [
        	'total_count' => 100, 
        	'total_pages' => 4, 
        	'current_page' => 1, 
        	'limit' => MAX_LIMIT
        	]]);

    	$data = json_decode($response->baseResponse->content())->data;
    	$this->assertCount(MAX_LIMIT, $data);
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

    /**
     * @test
     */
    public function it_fetches_all_emails_for_specific_mailbox()
    {

    	$emails = factory(\App\Email::class, 3)->create();
    	$emails = factory(\App\Email::class, 2)->create(['to' => 'test@example.com']);

    	$response = $this->json('GET', 'api/v1/emails?to=test@example.com');

        $response
            ->assertStatus(200);
    	$data = json_decode($response->baseResponse->content())->data;
    	$this->assertCount(2, $data);
    }

    /**
     * @test
     */
    public function it_fetches_whitch_body_type_is_available()
    {
        $exitCode = \Artisan::call('email:receive', ['file' => 'tests/storage/email.txt']);

        $response = $this->json('GET', 'api/v1/emails');
        $data = json_decode($response->baseResponse->content())->data;
        $response = $this->json('GET', 'api/v1/emails/'.$data[0]->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => 'Mail avec fichier attaché de 1ko', 'is_html' => true, 'is_text' => true]);
    }

    /**
     * @test
     */
    public function it_fetches_html_part_of_specific_email()
    {
    	$exitCode = \Artisan::call('email:receive', ['file' => 'tests/storage/email.txt']);

    	$response = $this->json('GET', 'api/v1/emails');
    	$data = json_decode($response->baseResponse->content())->data;
    	$response = $this->json('GET', 'api/v1/emails/'.$data[0]->id, [], ['Accept' => 'text/html']);
        $response
            ->assertStatus(200)
            ->assertSee('this is html part');
    }

    /**
     * @test
     */
    public function it_fetches_text_part_of_specific_email()
    {
    	$exitCode = \Artisan::call('email:receive', ['file' => 'tests/storage/email.txt']);

    	$response = $this->json('GET', 'api/v1/emails');
    	$data = json_decode($response->baseResponse->content())->data;
    	$response = $this->json('GET', 'api/v1/emails/'.$data[0]->id, [], ['Accept' => 'text/plain']);
        $response
            ->assertStatus(200)
            ->assertSee('this is text part');
    }


    /**
     * @test
     */
    public function it_fetches_raw_part_of_specific_email()
    {
    	$exitCode = \Artisan::call('email:receive', ['file' => 'tests/storage/email.txt']);

    	$response = $this->json('GET', 'api/v1/emails');
    	$data = json_decode($response->baseResponse->content())->data;
    	$response = $this->json('GET', 'api/v1/emails/'.$data[0]->id, [], ['Accept' => 'message/rfc822']);
        $response
            ->assertStatus(200)
            ->assertSee('this is html part')
            ->assertSee('this is text part');
    }
}
