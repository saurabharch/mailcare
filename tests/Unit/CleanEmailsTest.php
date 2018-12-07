<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Email;
use App\Attachment;
use App\Jobs\CleanEmails;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanEmailsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_can_clean_emails()
    {
        $email = factory(Email::class)->create();
        $emailSoftDeleted2MonthsAgo = factory(Email::class)->create([
            'deleted_at' => Carbon::now()->subMonths(2)->toDateString()
        ]);
        $emailSoftDeleted10DaysAgo = factory(Email::class)->create([
            'deleted_at' => Carbon::now()->subDays(10)->toDateString()
        ]);

        $this->assertTrue($email->exists);
        $this->assertTrue($emailSoftDeleted2MonthsAgo->exists);
        $this->assertTrue($emailSoftDeleted10DaysAgo->exists);
        $this->assertCount(3, Email::withTrashed()->get());

        $this->artisan('mailcare:clean-emails');

        $this->assertTrue($email->fresh()->exists);
        $this->assertNull($emailSoftDeleted2MonthsAgo->fresh());
        $this->assertTrue($emailSoftDeleted10DaysAgo->fresh()->exists);
        $this->assertCount(2, Email::withTrashed()->get());
    }

    /**
     * @test
     */
    public function it_can_clean_attachments()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_with_attachment.eml']
        )->assertExitCode(0);
        $email = Email::first();
        $email->deleted_at = Carbon::now()->subMonths(2)->toDateString();
        $email->save();

        $this->assertCount(1, Email::withTrashed()->get());
        $this->assertCount(1, Attachment::all());

        $this->artisan('mailcare:clean-emails');

        $this->assertNull($email->fresh());
        $this->assertCount(0, Email::withTrashed()->get());
        $this->assertCount(0, Attachment::all());
    }

    /**
     * @test
     */
    public function it_can_clean_files()
    {
        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_with_attachment.eml']
        )->assertExitCode(0);
        $email = Email::first();
        $email->deleted_at = Carbon::now()->subMonths(2)->toDateString();
        $email->save();

        $this->assertCount(1, Email::withTrashed()->get());
        $this->assertTrue(Storage::exists($email->path()));

        $this->artisan('mailcare:clean-emails');

        $this->assertNull($email->fresh());
        $this->assertCount(0, Email::withTrashed()->get());
        $this->assertFalse(Storage::exists($email->path()));
    }
}
