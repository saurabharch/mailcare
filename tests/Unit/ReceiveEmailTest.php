<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Artisan;

class ReceiveEmailTest extends TestCase
{
    use DatabaseMigrations;

    public function test_system_can_receive_email_from_file()
    {
        $exitCode = Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('emails', ['subject' => 'My first email']);
    }
}
