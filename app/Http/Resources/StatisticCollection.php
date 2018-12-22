<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Statistic;
use App\Traits\StorageForHuman;

class StatisticCollection extends ResourceCollection
{
    use StorageForHuman;

    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'emails_received' => Statistic::emailsReceived(),
                'inboxes_created' => Statistic::inboxesCreated(),
                'storage_used_for_human' => $this->humanFileSize(Statistic::storageUsed()),
                'storage_used' => Statistic::storageUsed(),
                'emails_deleted' => Statistic::emailsDeleted(),
            ],
        ];
    }
}
