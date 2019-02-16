<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Artisan;
use App\Email;
use Tests\Browser\Pages\ShowEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;

class DeleteEmailTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testDeleteEmail()
    {
        $email = factory(Email::class)->create();

        $this->browse(function (Browser $browser) use ($email) {
            $browser->visit('/')
                    ->waitForText('1 emails')
                    ->assertSee($email->subject)
                    ->visit(new ShowEmail($email))
            		->assertSeeEmail()
                    ->delete()
                    ->assertPathIs('/');;
        });
    }
}
