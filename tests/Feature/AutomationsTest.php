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

    public function subjectProvider()
    {
        return [
            ['My first email'],
            ['my First Email'],
            ['first'],
            ['my.*email'],
        ];
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testSubjectDoesMatch($subjectFilter)
    {
    	$automation = factory(Automation::class)->create([
            'subject' => $subjectFilter,
    	]);
    	$email = factory(Email::class)->create([
            'subject' => 'My first email',
    	]);

    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, ['Subject'], $email);
    }

    public function senderProvider()
    {
        return [
            ['test@example\.com'],
            ['TEST@example\.com'],
            ['@example'],
            ['test@.*\.com'],
        ];
    }

    /**
     * @dataProvider senderProvider
     */
    public function testSenderDoesMatch($senderFilter)
    {
        $sender = factory(Sender::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'sender_id' => $sender->id
        ]);

    	$automation = factory(Automation::class)->create([
            'sender' => $senderFilter,
    	]);


    	$this->handleAutomationListener($email);


		$this->assertAutomationTriggered($automation, ['Sender'], $email);
    }



    public function inboxProvider()
    {
        return [
            ['test@example\.com'],
            ['TEST@example\.com'],
            ['@example'],
            ['test@.*\.com'],
        ];
    }

    /**
     * @dataProvider inboxProvider
     */
    public function testInboxDoesMatch($inboxFilter)
    {
        $inbox = factory(Inbox::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'inbox_id' => $inbox->id
        ]);

    	$automation = factory(Automation::class)->create([
            'inbox' => $inboxFilter,
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
            'sender' => 'othertest@example\.com',
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

    public function wrongSubjectProvider()
    {
        return [
            ['My test email'],
            ['test$'],
            ['^My email$'],
        ];
    }

    /**
     * @dataProvider wrongSubjectProvider
     */
    public function testSubjectDoesntMatch($subjectFilter)
    {
    	$automation = factory(Automation::class)->create([
            'subject' => $subjectFilter,
    	]);
    	$email = factory(Email::class)->create([
            'subject' => 'My first email',
    	]);

    	$this->handleAutomationListener($email);


    	$this->assertAutomationNotTriggered($automation);
    }

    public function wrongSenderProvider()
    {
        return [
            ['othertest@example\.com'],
            ['example\.net$'],
            ['mytest.*'],
        ];
    }

    /**
     * @dataProvider wrongSenderProvider
     */
    public function testSenderDoesntMatch($senderFilter)
    {
        $sender = factory(Sender::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'sender_id' => $sender->id
        ]);

    	$automation = factory(Automation::class)->create([
            'sender' => $senderFilter,
    	]);


    	$this->handleAutomationListener($email);


    	$this->assertAutomationNotTriggered($automation);
    }

    public function wrongInboxProvider()
    {
        return [
            ['othertest@example\.com'],
            ['example\.net$'],
            ['mytest.*'],
        ];
    }

    /**
     * @dataProvider wrongInboxProvider
     */
    public function testInboxDoesntMatch($inboxFilter)
    {
        $inbox = factory(Inbox::class)->create(['email' => 'test@example.com']);
        $email = factory(Email::class)->create([
            'inbox_id' => $inbox->id
        ]);

    	$automation = factory(Automation::class)->create([
            'inbox' => $inboxFilter,
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

    public function testDeleteEmailAfterProcessingAutomation()
    {
        $automation = factory(Automation::class)->create([
            'subject' => 'Order completed',
            'action_delete_email' => true,
        ]);
        $email = factory(Email::class)->create([
            'subject' => 'Order completed',
        ]);

        $this->assertCount(1, Email::all());
        $this->handleAutomationListener($email);


        $this->assertAutomationTriggered($automation, ['Subject'], $email);
        $this->assertCount(0, Email::all());
    }

    public function testDontDeleteEmailIfAutomationDoesntMatch()
    {
        $automation = factory(Automation::class)->create([
            'subject' => 'Order cancelled',
            'action_delete_email' => false,
        ]);
        $automation = factory(Automation::class)->create([
            'subject' => 'Order completed',
        ]);
        $email = factory(Email::class)->create([
            'subject' => 'Order completed',
        ]);

        $this->assertCount(1, Email::all());
        $this->handleAutomationListener($email);


        $this->assertAutomationTriggered($automation, ['Subject'], $email);
        $this->assertCount(1, Email::all());
    }

    public function testDontDeleteEmailAfterProcessingAutomation()
    {
        $automation = factory(Automation::class)->create([
            'subject' => 'Order completed',
            'action_delete_email' => false,
        ]);
        $email = factory(Email::class)->create([
            'subject' => 'Order completed',
        ]);

        $this->handleAutomationListener($email);


        $this->assertCount(1, Email::all());
        $this->assertAutomationTriggered($automation, ['Subject'], $email);
        $this->assertCount(1, Email::all());
    }

    public function testFailed()
    {
        $this->client->shouldReceive('request')
            ->once()->andThrow(\Exception::class);
        $this->client->shouldReceive('request')
            ->once()->andReturn(true);
        $this->client->shouldReceive('request')
            ->once()->andThrow(\Exception::class);

        $automation = factory(Automation::class)->create([
            'subject' => 'Order completed',
        ]);

        $email = factory(Email::class)->create([
            'subject' => 'Order completed',
        ]);
        $this->handleAutomationListener($email);
        $this->assertTrue($automation->fresh()->in_error);

        $email = factory(Email::class)->create([
            'subject' => 'Order completed',
        ]);
        $this->handleAutomationListener($email);
        $this->assertFalse($automation->fresh()->in_error);

        $email = factory(Email::class)->create([
            'subject' => 'Order completed',
        ]);
        $this->handleAutomationListener($email);
        $this->assertTrue($automation->fresh()->in_error);
    }
}
