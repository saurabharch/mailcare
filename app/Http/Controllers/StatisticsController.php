<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Statistic;
use App\Transformers\StatisticTransformer;
use App\Traits\StorageForHuman;

class StatisticsController extends ApiController
{
    use StorageForHuman;

    public function index()
    {
        return $this->respond([
            'metadata' => [
                'emails_received' => Statistic::emailsReceived(),
                'inboxes_created' => Statistic::inboxesCreated(),
                'storage_used_for_human' => $this->human_filesize(Statistic::storageUsed()),
                'storage_used' => Statistic::storageUsed(),
            ],
            'data' => Statistic::oldest()->simplePaginate(100)->all()
        ]);
    }
}
