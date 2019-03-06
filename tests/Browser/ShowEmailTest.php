<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Pages\ShowEmail;
use \App\Email;
use Carbon\Carbon;
use App\User;

class ShowEmailTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testShowEmailWithoutAttachment()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_without_attachment.eml']
        );
        $email = Email::first();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')
                    ->waitForText('1 emails')
                    ->clickLink('My first email')
                    ->on(new ShowEmail($email))
                    ->assertSeeEmail()
                    ->assertSeeHtmlBody(function(Browser $browser){
                        $browser->assertSeeLink('mailcare.io')
                                ->assertSourceHas('My name is <strong>vincent</strong>.');
                    })
                    ->assertSeeTextBody(function(Browser $browser){
                        $browser->assertSee('Welcome to mailcare.io')
                                ->assertSee('sorry no link in plain text');
                    })
                    ->assertSeeRawBody(function(Browser $browser){
                        $browser->waitForText('Content-Transfer-Encoding')
                                ->assertSee('sorry no link in plain text')
                                ->assertSee('My name is <strong>vincent</strong>.')
                                ->assertSee('Content-Transfer-Encoding');
                    });
        });
    }

    public function testShowEmailWithAttachment()
    {
        $this->artisan(
            'mailcare:email-receive', 
            ['file' => 'tests/storage/email_with_attachment.eml']
        );
        $email = Email::first();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')
                    ->waitForText('1 email')
                    ->clickLink('Logo of MailCare')
                    ->on(new ShowEmail($email))
                    ->assertSeeEmail()
                    ->assertSeeHtmlBody(function(Browser $browser){
                        $browser->assertSourceHas('Please find attached the file you requested.<br />');
                    })
                    ->assertSeeTextBody(function(Browser $browser){
                        $browser->assertSee('Please find attached the file you requested.');
                    })
                    ->assertSeeRawBody(function(Browser $browser){
                        $browser->waitForText('logo-mailcare-renard.png')
                                ->assertSee('Please find attached the file you requested.');
                    });
        });
    }

    public function testFavoriteEmail()
    {
        $emailOne = factory(\App\Email::class)->create(['subject' => 'My first email']);
        $emailTwo = factory(\App\Email::class)->create(['subject' => 'My second email']);

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/')
                    ->waitForText('2 emails')
                    ->clickLink($emailTwo->subject)
                    ->on(new ShowEmail($emailTwo))
                    ->assertSeeEmail()
                    ->favorite()
                    ->visit('/')
                    ->clickLink('Favorite')
                    ->waitForText('1 emails')
                    ->assertSeeLink($emailTwo->subject)
                    ->assertDontSeeLink($emailOne->subject);
        });

    }

    public function testUnreadEmail()
    {
        $emailOne = factory(\App\Email::class)->create(['subject' => 'My first email']);
        $emailTwo = factory(\App\Email::class)->create(['subject' => 'My second email']);

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/')
                    ->waitForText('2 emails')
                    ->clickLink($emailOne->subject)
                    ->on(new ShowEmail($emailOne))
                    ->assertSeeEmail()
                    ->visit('/')
                    ->clickLink('Unread')
                    ->waitForText('1 emails')
                    ->assertSeeLink($emailTwo->subject)
                    ->assertDontSeeLink($emailOne->subject);
        });

    }

    public function testFilterEmails()
    {
        $emailOne = factory(\App\Email::class)->create(['subject' => 'My first email']);
        $emailTwo = factory(\App\Email::class)->create(['subject' => 'My second email']);

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/')
                    ->waitForText('2 emails')
                    ->type('input', 'my first')
                    ->waitForText('1 emails')
                    ->assertSeeLink($emailOne->subject)
                    ->assertDontSeeLink($emailTwo->subject)
                    ->type('input', 'email')
                    ->waitForText('2 emails')
                    ->assertSeeLink($emailOne->subject)
                    ->assertSeeLink($emailTwo->subject);
        });

    }

    public function testStatistics()
    {
        $emailOne = factory(\App\Email::class)->create(['subject' => 'My first email']);
        $emailTwo = factory(\App\Email::class)->create(['subject' => 'My second email']);

        $this->artisan(
            'mailcare:build-statistics', 
            ['date' => Carbon::now()->toDateString()]
        );

        $this->browse(function (Browser $browser) use ($emailOne, $emailTwo) {
            $browser->visit('/statistics')
                    ->waitForText('EMAILS RECEIVED')
                    ->assertSee('2');
        });

    }

    public function testFilterBySender()
    {
        $sender = factory(\App\Sender::class)->create(['email' => 'test@example.com']);

        $emails = factory(\App\Email::class, 2)->create();
        $emailFiltered = factory(\App\Email::class)->create([
            'sender_id' => $sender->id
        ]);

        $this->browse(function (Browser $browser) use ($emailFiltered) {
            $browser->visit('/')
                    ->waitForText('3 emails')
                    ->assertSeeLink($emailFiltered->subject)
                    ->clickLink($emailFiltered->subject)
                    ->on(new ShowEmail($emailFiltered))
                    ->assertSeeEmail()
                    ->clickLink($emailFiltered->sender->email)
                    ->waitForLocation('/senders/'.$emailFiltered->sender->email)
                    ->waitForText('1 emails')
                    ->assertSee('Filtered by sender')
                    ->assertSee($emailFiltered->sender->email);
        });

    }

    public function testFilterByInbox()
    {
        $inbox = factory(\App\Inbox::class)->create(['email' => 'test@example.com']);

        $emails = factory(\App\Email::class)->create();
        $emailFiltered = factory(\App\Email::class, 2)->create([
            'inbox_id' => $inbox->id
        ])->first();

        $this->browse(function (Browser $browser) use ($emailFiltered) {
            $browser->visit('/')
                    ->waitForText('3 emails')
                    ->assertSeeLink($emailFiltered->subject)
                    ->clickLink($emailFiltered->subject)
                    ->on(new ShowEmail($emailFiltered))
                    ->assertSeeEmail()
                    ->clickLink($emailFiltered->inbox->email)
                    ->waitForLocation('/inboxes/'.$emailFiltered->inbox->email)
                    ->waitForText('2 emails')
                    ->assertSee('Filtered by inbox')
                    ->assertSee($emailFiltered->inbox->email);
        });

    }
}
