<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;

trait Uuids
{
    protected static function bootUuids()
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4();
        });
    }
}
