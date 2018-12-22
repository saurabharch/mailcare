<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Statistic;
use App\Email;

class StatisticsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_fetches_statistics()
    {
        factory(Statistic::class)->create([
            'emails_received' => 10,
            'inboxes_created' => 2,
            'storage_used' => 20,
            'emails_deleted' => 0,
            'created_at' => Carbon::parse('20 august 2017')->toDateString(),
        ]);
        factory(Statistic::class)->create([
            'emails_received' => 5,
            'inboxes_created' => 4,
            'storage_used' => 30,
            'emails_deleted' => 3,
            'created_at' => Carbon::parse('11 february 2017')->toDateString(),
        ]);

        $response = $this->json('GET', 'api/statistics');

        $response
            ->assertStatus(200)

            ->assertJsonFragment([
                'emails_received' => 10,
                'inboxes_created' => 2,
                'storage_used' => 20,
                'emails_deleted' => 0,
                'created_at' => '2017-08-20',
            ])
            ->assertJsonFragment([
                'emails_received' => 5,
                'inboxes_created' => 4,
                'storage_used' => 30,
                'emails_deleted' => 3,
                'created_at' => '2017-02-11',
            ])
            ->assertJsonFragment([
                'emails_received' => 10 + 5,
                'inboxes_created' => 2 + 4,
                'storage_used' => 20 + 30,
                'emails_deleted' => 3,
                'storage_used_for_human' => 20 + 30 . '.00B',
            ]);
    }

    /**
     * @test
     */
    public function it_build_statistics()
    {
        $this->artisan('mailcare:build-statistics')->assertExitCode(0);

        $this->assertDatabaseHas('statistics', [
            'created_at' => Carbon::yesterday()->toDateString(),
            'emails_received' => 0,
            'inboxes_created' => 0,
            'storage_used' => 0,
            'emails_deleted' => 0,
        ]);
    }

    /**
     * @test
     */
    public function it_build_statistics_for_specific_date()
    {

        $email = factory(Email::class)->create();
        $email->delete();

        $this->artisan(
            'mailcare:email-receive',
            ['file' => 'tests/storage/email_without_attachment.eml']
        )->assertExitCode(0);

        $this->artisan(
            'mailcare:build-statistics',
            ['date' => Carbon::now()->toDateString()]
        )->assertExitCode(0);

        $this->assertDatabaseHas('statistics', [
            'created_at' => Carbon::now()->toDateString(),
            'emails_received' => 1,
            'inboxes_created' => 2,
            'storage_used' => 684,
            'emails_deleted' => 1,
        ]);
    }
}
