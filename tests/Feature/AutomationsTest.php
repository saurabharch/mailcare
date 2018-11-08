<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Automation;
use App\Email;
use App\Inbox;
use App\Sender;
use App\Attachment;
use App\Listeners\AutomationListener;
use App\Events\EmailReceived;
use Mockery;
use GuzzleHttp\Client;
use App\Http\Resources\EmailResource;

class AutomationsTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
    	parent::setUp();
        config(['mailcare.automations' => true]);
    	$this->client = Mockery::spy(Client::class);
    	app()->instance(Client::class, $this->client);
    }

    private function handleAutomationListener($email)
    {
    	$event = new EmailReceived($email);
    	$listener = resolve(AutomationListener::class);
    	$listener->handle($event);
    }

    private function assertAutomationNotTriggered($automation) 
    {
    	$this->assertEquals(0, $automation->fresh()->emails_received);
        $this->client->shouldNotHaveReceived('request');
    }

    private function assertAutomationTriggered($automation, $hits = [], $email)
    {
        $headers = [];
        $headers['X-MailCare-Title'] = $automation->title;
        if ($automation->action_secret_token) {
            $headers['X-MailCare-Secret-Token'] = $automation->action_secret_token;
        }
        foreach ($hits as $hit) {
            $headers['X-MailCare-'.$hit] = 'HIT';
        }

    	$this->assertEquals(1, $automation->fresh()->emails_received);
        $this->client->shouldHaveReceived('request')->with(
            'POST', 
            $automation->action_url,
            [
                'headers' => $headers,
                'form_params' => (new EmailResource($email))->response()->getData(),
            ]
        );
    }

    public function testSubjectDoesMatch()
    {
    	$automation = factory(Automation::class)->create([
            'subject' => 'My first email',
    	]);
    	$email = factory(Email::class)->create([
            'subject' => 'My first email',
    	]);

    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, ['Subject'], $email);
    }

    public function testSenderDoesMatch()
    {
        $sender = factory(Sender::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'sender_id' => $sender->id
        ]);

    	$automation = factory(Automation::class)->create([
            'sender' => 'test@example.com',
    	]);


    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, ['Sender'], $email);
    }

    public function testInboxDoesMatch()
    {
        $inbox = factory(Inbox::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'inbox_id' => $inbox->id
        ]);

    	$automation = factory(Automation::class)->create([
            'inbox' => 'test@example.com',
    	]);


    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, ['Inbox'], $email);
    }

    public function testHasAttachmentDoesMatch()
    {
		$email = factory(Email::class)->create();
        $attachment = factory(Attachment::class)->create(['email_id' => $email->id]);
        

    	$automation = factory(Automation::class)->create([
            'has_attachments' => true,
    	]);


    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, ['Has-Attachments'], $email);
    }

    public function testSubjectDoesMatchButSenderNot()
    {
        $sender = factory(Sender::class)->create(['email' => 'test@example.com']);

    	$automation = factory(Automation::class)->create([
            'subject' => 'My first email',
            'sender' => 'othertest@example.com',
    	]);
    	$email = factory(Email::class)->create([
            'subject' => 'My first email',
            'sender_id' => $sender->id
    	]);

    	$this->handleAutomationListener($email);


		$this->assertAutomationNotTriggered($automation);
    }

    public function testEmptyDoesMatch()
    {
    	$automation = factory(Automation::class)->create();
    	$email = factory(Email::class)->create();

    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, [], $email);
    }

    public function testSecretTokenSendedWhenAsked()
    {
    	$automation = factory(Automation::class)->create([
    		'action_secret_token' => 'secret1234',
    	]);
    	$email = factory(Email::class)->create();

    	$this->handleAutomationListener($email);

		$this->assertAutomationTriggered($automation, [], $email);
    }

    public function testSubjectDoesntMatch()
    {
    	$automation = factory(Automation::class)->create([
            'subject' => 'My test email',
    	]);
    	$email = factory(Email::class)->create([
            'subject' => 'My first email',
    	]);

    	$this->handleAutomationListener($email);


    	$this->assertAutomationNotTriggered($automation);
    }

    public function testSenderDoesntMatch()
    {
        $sender = factory(Sender::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'sender_id' => $sender->id
        ]);

    	$automation = factory(Automation::class)->create([
            'sender' => 'othertest@example.com',
    	]);


    	$this->handleAutomationListener($email);


    	$this->assertAutomationNotTriggered($automation);
    }

    public function testInboxDoesntMatch()
    {
        $inbox = factory(Inbox::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'inbox_id' => $inbox->id
        ]);

    	$automation = factory(Automation::class)->create([
            'inbox' => 'othertest@example.com',
    	]);


    	$this->handleAutomationListener($email);


    	$this->assertAutomationNotTriggered($automation);
    }

    public function testHasntAttachmentDoesntMatch()
    {
		$email = factory(Email::class)->create();

    	$automation = factory(Automation::class)->create([
            'has_attachments' => true,
    	]);


    	$this->handleAutomationListener($email);


    	$this->assertAutomationNotTriggered($automation);
    }
}
