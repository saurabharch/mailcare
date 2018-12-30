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
                'emails_received' => Statistic::metaEmailsReceived(),
                'inboxes_created' => Statistic::metaInboxesCreated(),
                'storage_used_for_human' => $this->humanFileSize(Statistic::metaStorageUsed()),
                'storage_used' => Statistic::metaStorageUsed(),
                'emails_deleted' => Statistic::metaEmailsDeleted(),
            ],
        ];
    }
}
