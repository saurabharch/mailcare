<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    public $timestamps = false;
    public $fillable = ['created_at', 'emails_received', 'inboxes_created', 'storage_used'];
    protected $casts = [
        'emails_received' => 'int',
        'inboxes_created' => 'int',
        'storage_used' => 'int',
    ];
    protected $hidden = ['id'];

    public static function emailsReceived(): int
    {
        return self::sum('emails_received');
    }

    public static function inboxesCreated(): int
    {
        return self::sum('inboxes_created');
    }

    public static function storageUsed(): int
    {
        return self::sum('storage_used');
    }
}
