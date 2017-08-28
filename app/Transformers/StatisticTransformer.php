<?php 

namespace App\Transformers;

class StatisticTransformer extends Transformer {

    public function transform($statistic)
    {
        return [
            'created_at' => $statistic['created_at'],
            'emails_received' => (int) $statistic['emails_received'],
            'inboxes_created' => (int) $statistic['inboxes_created'],
            'storage_used' => (int) $statistic['storage_used'],
        ];
    }
}