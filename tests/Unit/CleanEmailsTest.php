<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Email;
use App\Attachment;
use App\Statistic;
use App\Jobs\CleanEmails;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mockery;
use App\Console\Commands\AutoSoftDeleteEmails;

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
            'deleted_at' => Carbon::now()->subMonths(2)
        ]);
        $emailSoftDeleted10DaysAgo = factory(Email::class)->create([
            'deleted_at' => Carbon::now()->subDays(10)
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
        $email->deleted_at = Carbon::now()->subMonths(2);
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
        $email->deleted_at = Carbon::now()->subMonths(2);
        $email->save();

        $this->assertCount(1, Email::withTrashed()->get());
        $this->assertTrue(Storage::exists($email->path()));

        $this->artisan('mailcare:clean-emails');

        $this->assertNull($email->fresh());
        $this->assertCount(0, Email::withTrashed()->get());
        $this->assertFalse(Storage::exists($email->path()));
    }

    /**
     * @test
     */
    public function it_dont_soft_delete_emails_if_not_necessary()
    {
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();
        $command->shouldReceive([
            'getSizeToDelete' => 0
        ]);

        $email = factory(Email::class)->create();

        $this->assertCount(1, Email::all());
        $this->assertCount(1, Email::withTrashed()->get());

        $command->handle();

        $this->assertCount(1, Email::all());
        $this->assertCount(1, Email::withTrashed()->get());
    }

    /**
     * @test
     */
    public function it_can_automatically_soft_delete_emails_by_oldest()
    {
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();
        $command->shouldReceive([
            'getSizeToDelete' => 10
        ]);

        $email1 = factory(Email::class)->create([
            'created_at' => '2018-08-15',
            'size_in_bytes' => 10
        ]);
        $email2 = factory(Email::class)->create([
            'created_at' => '2018-09-15',
            'size_in_bytes' => 20
        ]);

        $this->assertCount(2, Email::all());
        $this->assertCount(2, Email::withTrashed()->get());

        $command->handle();

        $this->assertCount(1, Email::all());
        $this->assertCount(2, Email::withTrashed()->get());
        $this->assertTrue($email1->refresh()->trashed());
        $this->assertFalse($email2->refresh()->trashed());
    }

    /**
     * @test
     */
    public function it_dont_delete_favorite_email()
    {
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();
        $command->shouldReceive([
            'getSizeToDelete' => 10
        ]);

        $email1 = factory(Email::class)->create([
            'created_at' => '2018-08-15',
            'favorite' => true,
            'size_in_bytes' => 10
        ]);
        $email2 = factory(Email::class)->create([
            'created_at' => '2018-09-15',
            'size_in_bytes' => 20
        ]);

        $this->assertCount(2, Email::all());
        $this->assertCount(2, Email::withTrashed()->get());

        $command->handle();

        $this->assertCount(1, Email::all());
        $this->assertCount(2, Email::withTrashed()->get());
        $this->assertFalse($email1->refresh()->trashed());
        $this->assertTrue($email2->refresh()->trashed());
    }

    /**
     * @test
     */
    public function get_size_to_delete_when_storage_used_increase()
    {
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 20,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 30,
            'created_at' => Carbon::now()->subDays(15),
        ]);
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();

        $command->shouldReceive([
            'getDiskFreeSpace' => 30,
        ]);

        $this->assertEquals(45, $command->getSizeToDelete());
    }


    /**
     * @test
     */
    public function get_size_to_delete_when_storage_used_decrease()
    {
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 100,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 10,
            'created_at' => Carbon::now()->subDays(15),
        ]);
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();

        $command->shouldReceive([
            'getDiskFreeSpace' => 1,
        ]);

        $this->assertEquals(1, $command->getSizeToDelete());
    }


    /**
     * @test
     */
    public function get_size_to_delete_when_no_data_for_the_first_period()
    {
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 10,
            'created_at' => Carbon::now()->subDays(15),
        ]);
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();

        $command->shouldReceive([
            'getDiskFreeSpace' => 100,
        ]);

        $this->assertEquals(0, $command->getSizeToDelete());
    }


    /**
     * @test
     */
    public function get_size_to_delete_when_no_data_for_the_second_period()
    {
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 10,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();

        $command->shouldReceive([
            'getDiskFreeSpace' => 100,
        ]);

        $this->assertEquals(0, $command->getSizeToDelete());
    }


    /**
     * @test
     */
    public function get_size_to_delete_when_enough_storage_used()
    {
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 100,
            'created_at' => Carbon::now()->subDays(40),
        ]);
        factory(Statistic::class)->create([
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 120,
            'created_at' => Carbon::now()->subDays(15),
        ]);
        $command = Mockery::mock(AutoSoftDeleteEmails::class)->makePartial();

        $command->shouldReceive([
            'getDiskFreeSpace' => 150,
        ]);

        $this->assertEquals(0, $command->getSizeToDelete());
    }
}
