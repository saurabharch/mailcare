<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class HomePageTest extends DuskTestCase
{
    public function testHomePage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('MailCare');
        });
    }
}
