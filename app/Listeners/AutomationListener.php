<?php

namespace App\Listeners;

use App\Events\EmailReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Automation;
use GuzzleHttp\Client;

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

            if (!empty($automation->subject)) {
                if ($automation->subject != $event->email->subject) {
                    continue;
                }
            }
            if (!empty($automation->sender)) {
                if ($automation->sender != $event->email->sender->email) {
                    continue;
                }
            }
            if (!empty($automation->inbox)) {
                if ($automation->inbox != $event->email->inbox->email) {
                    continue;
                }
            }
            if ($automation->has_attachments) {
                if ($event->email->attachments->count() == 0) {
                    continue;
                }
            }

            $automation->increment('emails_received');

            if ($automation->action_secret_token) {
                $headers = ['headers' => ['X-Mailcare-Token' => $automation->action_secret_token]];
                $res = $this->client->request('GET', $automation->action_url, $headers);
            }
            else {
                $res = $this->client->request('GET', $automation->action_url);
            }
        }
    }
}
