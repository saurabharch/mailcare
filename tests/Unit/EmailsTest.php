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

        $data = $response->getData()->data;
        $this->assertEquals($emailTwo->subject, $data[0]->subject);
        $this->assertEquals($emailOne->subject, $data[1]->subject);
    }

    /**
     * @test
     */
    public function it_fetches_limited_emails_per_default()
    {
        define('MAX_LIMIT', 25);
        $emails = factory(\App\Email::class, 28)->create();

        $response = $this->json('GET', 'api/v1/emails');

        $response->assertStatus(200)->assertJsonFragment(['paginator' => [
            'total_count' => 28,
            'total_pages' => 2,
            'current_page' => 1,
            'limit' => MAX_LIMIT
            ]]);

        $this->assertCount(MAX_LIMIT, $response->getData()->data);
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

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_for_specific_inbox()
    {
        $inbox = factory(\App\Inbox::class)->create(['email' => 'test@example.com']);

        $emails = factory(\App\Email::class, 3)->create();
        $emails = factory(\App\Email::class, 2)->create([
            'inbox_id' => $inbox->id
        ]);

        $response = $this->json('GET', 'api/v1/emails?inbox=test@example.com');

        $response->assertStatus(200);

        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_for_specific_sender()
    {
        $sender = factory(\App\Sender::class)->create(['email' => 'test@example.com']);

        $emails = factory(\App\Email::class, 3)->create();
        $emails = factory(\App\Email::class, 2)->create([
            'sender_id' => $sender->id
        ]);

        $response = $this->json('GET', 'api/v1/emails?sender=test@example.com');

        $response->assertStatus(200);

        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_starting_with_the_query_term_for_a_search()
    {
        $matchingInbox = factory(\App\Inbox::class)->create(['email' => 'matching-to@example.com']);
        $matchingSender = factory(\App\Sender::class)->create(['email' => 'matching-from@example.com']);

        factory(\App\Email::class, 5)->create();

        factory(\App\Email::class)->create([
            'sender_id' => $matchingSender->id
        ]);
        factory(\App\Email::class)->create([
            'inbox_id' => $matchingInbox->id
        ]);
        factory(\App\Email::class)->create([
            'subject' => 'matching subject'
        ]);

        $response = $this->json('GET', 'api/v1/emails?search=matching');

        $response->assertStatus(200);
        $this->assertCount(3, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_with_the_query_term_inside_for_a_search()
    {
        $matchingInbox = factory(\App\Inbox::class)->create(['email' => 'email-matching-to@example.com']);
        $matchingSender = factory(\App\Sender::class)->create(['email' => 'email-matching-from@example.com']);

        factory(\App\Email::class, 5)->create();

        factory(\App\Email::class)->create([
            'sender_id' => $matchingSender->id
        ]);
        factory(\App\Email::class)->create([
            'inbox_id' => $matchingInbox->id
        ]);
        factory(\App\Email::class)->create([
            'subject' => 'a matching subject'
        ]);

        $response = $this->json('GET', 'api/v1/emails?search=matching');

        $response->assertStatus(200);
        $this->assertCount(3, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_unread()
    {
        $emails = factory(\App\Email::class)->create();
        $emails = factory(\App\Email::class, 2)->create(['read' => Carbon::now()]);

        $response = $this->json('GET', 'api/v1/emails?unread=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->getData()->data);
    }


    /**
     * @test
     */
    public function it_fetches_all_emails_favorites()
    {
        $emails = factory(\App\Email::class)->create();
        $emails = factory(\App\Email::class, 2)->create(['favorite' => true]);

        $response = $this->json('GET', 'api/v1/emails?favorite=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_whitch_body_type_is_available()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => 'My first email', 'has_html' => true, 'has_text' => true]);
    }

    /**
     * @test
     */
    public function it_fetches_html_part_of_specific_email()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/html']);
        $response
            ->assertStatus(200)
            ->assertSee('<a href="https://mailcare.io">mailcare.io</a>')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * @test
     */
    public function it_fetches_text_part_of_specific_email()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/plain']);
        $response
            ->assertStatus(200)
            ->assertSee('sorry no link in plain text.')
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }


    /**
     * @test
     */
    public function it_fetches_raw_part_of_specific_email()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'message/rfc2822']);
        $response
            ->assertStatus(200)
            ->assertSee('Welcome to &lt;a href=&quot;https://mailcare.io&quot;&gt;mailcare.io&lt;/a&gt;')
            ->assertSee('Welcome to mailcare.io, sorry no link in plain text.')
            ->assertHeader('Content-Type', 'message/rfc2822; charset=UTF-8');
    }


    /**
     * @test
     */
    public function it_fetches_html_part_when_i_prefer_it()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/plain; q=0.5, text/html']);
        $response
            ->assertStatus(200)
            ->assertSee('<a href="https://mailcare.io">mailcare.io</a>')
            ->assertDontSee('sorry no link in plain text.')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }


    /**
     * @test
     */
    public function it_fetches_text_part_when_i_prefer_it()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/html; q=0.5, text/plain']);
        $response
            ->assertStatus(200)
            ->assertSee('sorry no link in plain text.')
            ->assertDontSee('<a href="https://mailcare.io">mailcare.io</a>');
    }

    /**
     * @test
     */
    public function it_return_not_acceptable_when_i_fetches_unsupported_accept()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'message/rfc822']);
        $response
            ->assertStatus(406)
            ->assertDontSee('this is text part')
            ->assertDontSee('this is html part');
    }

    /**
     * @test
     */
    public function email_can_be_read()
    {
        $email = factory(\App\Email::class)->create();

        $response = $this->json('GET', 'api/v1/emails/'.$email->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['read' => null]);

        $response = $this->json('GET', 'api/v1/emails/'.$email->id);

        $response
            ->assertStatus(200)
            ->assertJsonMissing(['read' => null]);
    }

    /**
     * @test
     */
    public function email_can_be_favorite()
    {
        $email = factory(\App\Email::class)->create();

        $response = $this->json('GET', 'api/v1/emails/'.$email->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['favorite' => false]);

        $response = $this->json('POST', 'api/v1/emails/'.$email->id.'/favorite');

        $response = $this->json('GET', 'api/v1/emails/'.$email->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['favorite' => true]);

        $response = $this->json('DELETE', 'api/v1/emails/'.$email->id.'/favorite');


        $response = $this->json('GET', 'api/v1/emails/'.$email->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['favorite' => false]);
    }

    /**
     * @test
     */
    public function it_fetches_attachments_of_email()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_with_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $response = $this->json('GET', 'api/v1/emails/'.$response->getData()->data[0]->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'file_name' => 'logo-mailcare-renard.png',
                'content_type' => 'image/png',
                'size_in_bytes' => '111766',
                ]);
    }

    /**
     * @test
     */
    public function it_download_attachments_of_email()
    {
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_with_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $emailId = $response->getData()->data[0]->id;
        $response = $this->json('GET', 'api/v1/emails/'.$emailId);
        $attachmentId = $response->getData()->data->attachments[0]->id;
        $response = $this->json('GET', 'api/v1/emails/'.$emailId.'/attachments/'.$attachmentId);

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'image/png');
    }

    /**
     * @test
     */
    public function it_download_attachments_of_email_that_doesnt_exist()
    {
        $response = $this->json('GET', 'api/v1/emails/id-doesnt-exist/attachments/id-doesnt-exist');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_download_attachments_that_doesnt_exist_of_email()
    {
        $email = factory(\App\Email::class)->create();

        $response = $this->json('GET', 'api/v1/emails/'.$email->id.'/attachments/id-doesnt-exist');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_download_attachments_that_doesnt_exist_on_disk()
    {
        $this->withoutExceptionHandling();
        $exitCode = \Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_with_attachment.eml']);

        $response = $this->json('GET', 'api/v1/emails');
        $emailId = $response->getData()->data[0]->id;

        $attachment = factory(\App\Attachment::class)->create(['email_id' => $emailId]);

        $response = $this->json('GET', 'api/v1/emails/'.$attachment->email->id.'/attachments/'.$attachment->id);

        $response->assertStatus(404);
    }
}
