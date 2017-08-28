<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Statistic;
use App\Transformers\StatisticTransformer;

class StatisticsController extends ApiController
{
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

    public function human_filesize($bytes, $dec = 2) 
    {
        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f", $bytes / pow(1000, $factor)) . @$size[$factor];
    }
}
