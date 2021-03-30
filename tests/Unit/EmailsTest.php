<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use \Carbon\Carbon;
use App\Email;

class EmailsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_fetches_latest_emails()
    {
        $emailOne = factory(Email::class)->create([
            'subject' => 'My first email',
            'created_at' => Carbon::yesterday()
            ]);
        $emailTwo = factory(Email::class)->create([
            'subject' => 'My second email',
            'created_at' => Carbon::now()
            ]);

        $response = $this->json('GET', 'api/emails');

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
        factory(Email::class, 28)->create();

        $response = $this->json('GET', 'api/emails');

        $response->assertStatus(200)->assertJson(['meta' => [
            'total' => 28,
            'last_page' => 2,
            'current_page' => 1,
            'per_page' => MAX_LIMIT
            ]]);

        $this->assertCount(MAX_LIMIT, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_a_single_email()
    {
        $email = factory(Email::class)->create();

        $response = $this->json('GET', 'api/emails/'.$email->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => $email->subject]);
    }

    /**
     * @test
     */
    public function it_fetches_an_email_that_doesnt_exist()
    {
        $response = $this->json('GET', 'api/emails/id-doesnt-exist');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_for_specific_inbox()
    {
        $inbox = factory(\App\Inbox::class)->create(['email' => 'test@example.com']);

        factory(Email::class, 3)->create();
        factory(Email::class, 2)->create([
            'inbox_id' => $inbox->id
        ]);

        $response = $this->json('GET', 'api/emails?inbox=test@example.com');

        $response->assertStatus(200);

        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_for_specific_sender()
    {
        $sender = factory(\App\Sender::class)->create(['email' => 'test@example.com']);

        factory(Email::class, 3)->create();
        factory(Email::class, 2)->create([
            'sender_id' => $sender->id
        ]);

        $response = $this->json('GET', 'api/emails?sender=test@example.com');

        $response->assertStatus(200);

        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_for_specific_subject()
    {
        factory(Email::class)->create(['subject' => 'welcome']);
        factory(Email::class)->create(['subject' => 'Welcome!']);
        factory(Email::class)->create(['subject' => 'Bye']);

        $response = $this->json('GET', 'api/emails?subject=welcome');

        $response->assertStatus(200);

        $this->assertCount(1, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_for_a_subject_with_a_joker()
    {
        factory(Email::class)->create(['subject' => 'welcome']);
        factory(Email::class)->create(['subject' => 'Welcome!']);
        factory(Email::class)->create(['subject' => 'Bye']);

        $response = $this->json('GET', 'api/emails?subject=welcome*');

        $response->assertStatus(200);

        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_since_a_specific_date()
    {
        factory(Email::class)->create(['created_at' => Carbon::now()->subMonths(3)]);
        factory(Email::class)->create(['created_at' => Carbon::now()->subMonths(1)]);

        $since = Carbon::now()->subMonths(2)->toIso8601String();
        $response = $this->json('GET', "api/emails?since=$since");

        $response->assertStatus(200);

        $this->assertCount(1, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_starting_with_the_query_term_for_a_search()
    {
        $matchingInbox = factory(\App\Inbox::class)->create(['email' => 'matching-to@example.com']);
        $matchingSender = factory(\App\Sender::class)->create(['email' => 'matching-from@example.com']);

        factory(Email::class, 5)->create();

        factory(Email::class)->create([
            'sender_id' => $matchingSender->id
        ]);
        factory(Email::class)->create([
            'inbox_id' => $matchingInbox->id
        ]);
        factory(Email::class)->create([
            'subject' => 'matching subject'
        ]);

        $response = $this->json('GET', 'api/emails?search=matching');

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

        factory(Email::class, 5)->create();

        factory(Email::class)->create([
            'sender_id' => $matchingSender->id
        ]);
        factory(Email::class)->create([
            'inbox_id' => $matchingInbox->id
        ]);
        factory(Email::class)->create([
            'subject' => 'a matching subject'
        ]);

        $response = $this->json('GET', 'api/emails?search=matching');

        $response->assertStatus(200);
        $this->assertCount(3, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_all_emails_unread()
    {
        factory(Email::class)->create();
        factory(Email::class, 2)->create(['read' => Carbon::now()]);

        $response = $this->json('GET', 'api/emails?unread=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->getData()->data);
    }


    /**
     * @test
     */
    public function it_fetches_all_emails_favorites()
    {
        factory(Email::class)->create();
        factory(Email::class, 2)->create(['favorite' => true]);

        $response = $this->json('GET', 'api/emails?favorite=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->getData()->data);
    }

    /**
     * @test
     */
    public function it_fetches_whitch_body_type_is_available()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => 'My first email', 'has_html' => true, 'has_text' => true])
            ->assertHeader('Content-Type', 'application/json');
    }

    /**
     * @test
     */
    public function it_fetches_the_good_version()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'application/vnd.mailcare.v1+json']);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => 'My first email', 'has_html' => true, 'has_text' => true])
            ->assertHeader('Content-Type', 'application/vnd.mailcare.v1+json; charset=UTF-8');
    }

    /**
     * @test
     */
    public function it_fetches_html_part_of_specific_email()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/html']);
        $response
            ->assertStatus(200)
            ->assertSee('<a href="https://mailcare.io">mailcare.io</a>', false)
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * @test
     */
    public function it_fetches_text_part_of_specific_email()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/plain']);
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
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'message/rfc2822']);
        $response
            ->assertStatus(200)
            ->assertSee('Welcome to &lt;a href=&quot;https://mailcare.io&quot;&gt;mailcare.io&lt;/a&gt;', false)
            ->assertSee('Welcome to mailcare.io, sorry no link in plain text.')
            ->assertHeader('Content-Type', 'message/rfc2822; charset=UTF-8');
    }


    /**
     * @test
     */
    public function it_fetches_html_part_when_i_prefer_it()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/plain; q=0.5, text/html']);
        $response
            ->assertStatus(200)
            ->assertSee('<a href="https://mailcare.io">mailcare.io</a>', false)
            ->assertDontSee('sorry no link in plain text.')
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }


    /**
     * @test
     */
    public function it_fetches_text_part_when_i_prefer_it()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'text/html; q=0.5, text/plain']);
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
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id, [], ['Accept' => 'message/rfc822']);
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
        $email = factory(Email::class)->create();

        $response = $this->json('GET', 'api/emails');

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $email->id, 'read' => null]);

        $response = $this->json('GET', 'api/emails/'.$email->id);

        $response
            ->assertStatus(200)
            ->assertJsonMissing(['read' => null]);

        $response = $this->json('GET', 'api/emails');

        $response
            ->assertStatus(200)
            ->assertJsonMissingExact(['id' => $email->id, 'read' => null]);
    }

    /**
     * @test
     */
    public function email_can_be_favorite()
    {
        $email = factory(Email::class)->create();

        $response = $this->json('GET', 'api/emails/'.$email->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['favorite' => false]);

        $this->json('POST', 'api/emails/'.$email->id.'/favorites');

        $response = $this->json('GET', 'api/emails/'.$email->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['favorite' => true]);

        $this->json('DELETE', 'api/emails/'.$email->id.'/favorites');


        $response = $this->json('GET', 'api/emails/'.$email->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['favorite' => false]);
    }

    /**
     * @test
     */
    public function it_fetches_attachments_of_email()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_with_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $response = $this->json('GET', 'api/emails/'.$response->getData()->data[0]->id);

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
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_with_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $emailId = $response->getData()->data[0]->id;
        $response = $this->json('GET', 'api/emails/'.$emailId);
        $attachmentId = $response->getData()->data->attachments[0]->id;
        $response = $this->json('GET', 'api/emails/'.$emailId.'/attachments/'.$attachmentId);

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'image/png');
    }

    /**
     * @test
     */
    public function it_download_attachments_of_email_that_doesnt_exist()
    {
        $response = $this->json('GET', 'api/emails/id-doesnt-exist/attachments/id-doesnt-exist');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_download_attachments_that_doesnt_exist_of_email()
    {
        $email = factory(Email::class)->create();

        $response = $this->json('GET', 'api/emails/'.$email->id.'/attachments/id-doesnt-exist');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_download_attachments_that_doesnt_exist_on_disk()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_with_attachment.eml']
        );

        $response = $this->json('GET', 'api/emails');
        $emailId = $response->getData()->data[0]->id;

        $attachment = factory(\App\Attachment::class)->create(['email_id' => $emailId]);

        $response = $this->json('GET', 'api/emails/'.$attachment->email->id.'/attachments/'.$attachment->id);

        $response->assertStatus(404);
    }
}
