<?php

namespace App\Transformers;

class StatisticTransformer extends Transformer
{

    public function transform($statistic)
    {
        return [
            'created_at' => $statistic->created_at,
            'emails_received' => $statistic->emails_received,
            'inboxes_created' => $statistic->inboxes_created,
            'storage_used' => $statistic->storage_used,
        ];
    }
}
