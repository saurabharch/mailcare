<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use \Carbon\Carbon;
use Artisan;

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
    		]);
        factory(\App\Statistic::class)->create([
            'emails_received' => 5,
            'inboxes_created' => 4,
            'storage_used' => 30,
            ]);

    	$response = $this->json('GET', 'api/v1/statistics');

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['emails_received' => 15])
            ->assertJsonFragment(['inboxes_created' => 6])
            ->assertJsonFragment(['storage_used' => 50]);
    }

    /**
     * @test
     */
    public function it_build_statistics()
    {
        $exitCode = Artisan::call('build:statistics');

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('statistics', ['created_at' => Carbon::yesterday()->toDateString(),'emails_received' => 0, 'inboxes_created' => 0, 'storage_used' => 0]);
        
    }

    /**
     * @test
     */
    public function it_build_statistics_for_specific_date()
    {
        $exitCode = Artisan::call('email:receive', ['file' => 'tests/storage/email.txt']);

        $exitCode = Artisan::call('build:statistics', ['date' => Carbon::now()->toDateString()]);

        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseHas('statistics', ['created_at' => Carbon::now()->toDateString(),'emails_received' => 1, 'inboxes_created' => 1, 'storage_used' => 2282]);
        
    }

}
