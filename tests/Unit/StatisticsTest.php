<?php

namespace Tests\Unit;

use Artisan;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StatisticsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function it_fetches_statistics()
    {
        factory(\App\Statistic::class)->create([
            'emails_received' => 10,
            'inboxes_created' => 2,
            'storage_used' => 20,
            'created_at' => Carbon::parse('20 august 2017')->toDateString(),
        ]);
        factory(\App\Statistic::class)->create([
            'emails_received' => 5,
            'inboxes_created' => 4,
            'storage_used' => 30,
            'created_at' => Carbon::parse('11 february 2017')->toDateString(),
        ]);

        $response = $this->json('GET', 'api/v1/statistics');

        $response
            ->assertStatus(200)

            ->assertJsonFragment([
                'emails_received' => 10,
                'inboxes_created' => 2,
                'storage_used' => 20,
                'created_at' => '2017-08-20',
            ])
            ->assertJsonFragment([
                'emails_received' => 5,
                'inboxes_created' => 4,
                'storage_used' => 30,
                'created_at' => '2017-02-11',
            ])
            ->assertJsonFragment([
                'emails_received' => 10 + 5,
                'inboxes_created' => 2 + 4,
                'storage_used' => 20 + 30,
                'storage_used_for_human' => 20 + 30 . '.00B',
            ]);
    }

    /**
     * @test
     */
    public function it_build_statistics()
    {
        $exitCode = Artisan::call('mailcare:build-statistics');

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('statistics', ['created_at' => Carbon::yesterday()->toDateString(),'emails_received' => 0, 'inboxes_created' => 0, 'storage_used' => 0]);
    }

    /**
     * @test
     */
    public function it_build_statistics_for_specific_date()
    {
        $exitCode = Artisan::call('mailcare:email-receive', ['file' => 'tests/storage/email_without_attachment.eml']);

        $exitCode = Artisan::call('mailcare:build-statistics', ['date' => Carbon::now()->toDateString()]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('statistics', ['created_at' => Carbon::now()->toDateString(),'emails_received' => 1, 'inboxes_created' => 1, 'storage_used' => 684]);
    }
}
