<?php

namespace App\Listeners;

use App\Events\EmailReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Automation;
use GuzzleHttp\Client;
use App\Http\Resources\EmailResource;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForwardEmail;

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

        $actionDeleteEmail = false;
        foreach ($automations as $automation) {
            $headers = [];
            $headers['X-MailCare-Title'] = $automation->title;

            if (!empty($automation->subject)) {
                if (preg_match('#'.$automation->subject.'#i', $event->email->subject)) {
                    $headers['X-MailCare-Subject'] = 'HIT';
                } else {
                    continue;
                }
            }
            if (!empty($automation->sender)) {
                if (preg_match('#'.$automation->sender.'#i', $event->email->sender->email)) {
                    $headers['X-MailCare-Sender'] = 'HIT';
                } else {
                    continue;
                }
            }
            if (!empty($automation->inbox)) {
                if (preg_match('#'.$automation->inbox.'#i', $event->email->inbox->email)) {
                    $headers['X-MailCare-Inbox'] = 'HIT';
                } else {
                    continue;
                }
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
            if ($automation->action_delete_email && $actionDeleteEmail == false) {
                $actionDeleteEmail = true;
            }

            try {
                if ($automation->post_raw) {
                    $this->client->request('POST', $automation->action_url, [
                        'headers' => $headers,
                        'form_params' => file_get_contents($event->email->fullPath()),
                    ]);
                } else {
                    $this->client->request('POST', $automation->action_url, [
                        'headers' => $headers,
                        'form_params' => (new EmailResource($event->email))->response()->getData(),
                    ]);
                }
                $automation->in_error = false;
            } catch (\Exception $e) {
                $automation->in_error = true;
            }

            if (config('mailcare.forward') && !empty($automation->action_email)) {
                Mail::to($automation->action_email)->send(new ForwardEmail($event->email));
            }

            $automation->save();
        }

        if ($actionDeleteEmail) {
            $event->email->delete();
        }
    }
}
