<?php

namespace App\Listeners;

use App\Events\EmailReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Automation;
use GuzzleHttp\Client;
use App\Http\Resources\EmailResource;

class AutomationListener
{
    protected $client;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Handle the event.
     *
     * @param  EmailReceived  $event
     * @return void
     */
    public function handle(EmailReceived $event)
    {
        if (!config('mailcare.automations')) {
            return;
        }

        $automations = Automation::all();

        foreach ($automations as $automation) {
            $headers = [];
            $headers['X-MailCare-Title'] = $automation->title;

            if (!empty($automation->subject)) {
                if ($automation->subject != $event->email->subject) {
                    continue;
                }
                $headers['X-MailCare-Subject'] = 'HIT';
            }
            if (!empty($automation->sender)) {
                if ($automation->sender != $event->email->sender->email) {
                    continue;
                }
                $headers['X-MailCare-Sender'] = 'HIT';
            }
            if (!empty($automation->inbox)) {
                if ($automation->inbox != $event->email->inbox->email) {
                    continue;
                }
                $headers['X-MailCare-Inbox'] = 'HIT';
            }
            if ($automation->has_attachments) {
                if ($event->email->attachments->count() == 0) {
                    continue;
                }
                $headers['X-MailCare-Has-Attachments'] = 'HIT';
            }

            if ($automation->action_secret_token) {
                $headers['X-MailCare-Secret-Token'] = $automation->action_secret_token;
            }

            $automation->increment('emails_received');

            $this->client->request('POST', $automation->action_url, [
                'headers' => $headers,
                'form_params' => (new EmailResource($event->email))->response()->getData(),
            ]);
        }
    }
}
