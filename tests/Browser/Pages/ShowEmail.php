<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class ShowEmail extends BasePage
{
    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function url()
    {
        return "/emails/{$this->email->id}";
    }

    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

    public function assertSeeEmail(Browser $browser)
    {
        $browser->waitForText($this->email->subject)
                    ->assertSeeIn('h1', $this->email->subject)
                    ->waitForText($this->email->attachments->count().' attachments');
    }

    public function assertSeeHtmlBody(Browser $browser, callable $callback)
    {
        $browser->waitFor('li.is-active[data-label="Html"]');
        $browser->driver->switchTo()->frame('iframe-body');

        $callback($browser);

        $browser->driver->switchTo()->defaultContent();
    }

    public function assertSeeTextBody(Browser $browser, callable $callback)
    {
        $browser->clickLink('Text');
        $browser->waitFor('li.is-active[data-label="Text"]');

        $browser->driver->switchTo()->frame('iframe-body');

        $callback($browser);

        $browser->driver->switchTo()->defaultContent();
    }

    public function assertSeeRawBody(Browser $browser, callable $callback)
    {
        $browser->clickLink('Raw');
        $browser->waitFor('li.is-active[data-label="Raw"]');

        $browser->driver->switchTo()->frame('iframe-body');

        $callback($browser);

        $browser->driver->switchTo()->defaultContent();
    }

    public function favorite(Browser $browser)
    {
        $browser->click('button');
    }

    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
