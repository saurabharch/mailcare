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
        $exitCode = Artisan::call('email:receive', ['file' => 'tests/storage/email.txt']);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('emails', ['subject' => 'Mail avec fichier attaché de 1ko']);
    }
}
