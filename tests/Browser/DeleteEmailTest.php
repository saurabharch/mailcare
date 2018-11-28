<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Artisan;
use App\Email;
use Tests\Browser\Pages\ShowEmail;

class DeleteEmailTest extends DuskTestCase
{
    public function testDeleteEmail()
    {
        Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);
        $email = Email::first();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit(new ShowEmail($email))
                    ->delete()
                    ->assertPathIs('/');;
        });
    }
}
