<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Statistic;
use App\Transformers\StatisticTransformer;
use App\Traits\StorageForHuman;

class StatisticsController extends ApiController
{
    use StorageForHuman;
    
    protected $statisticTransformer;

    public function __construct(StatisticTransformer $statisticTransformer)
    {
        $this->statisticTransformer = $statisticTransformer;
    }

    public function index()
    {
    	return $this->respond([
                'metadata' => [
        			'emails_received' => (int) Statistic::emailsReceived(), 
        			'inboxes_created' => (int) Statistic::inboxesCreated(),
                    'storage_used_for_human' => $this->human_filesize((int) Statistic::storageUsed()),
        			'storage_used' => (int) Statistic::storageUsed(),
    			],
                'data' => $this->statisticTransformer->transformCollection(Statistic::paginate(100)->all()),
                ]);
    }
}
