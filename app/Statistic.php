<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    public $timestamps = false;
    public $fillable = [
        'created_at',
        'emails_received',
        'inboxes_created',
        'storage_used',
        'emails_deleted',
    ];
    protected $casts = [
        'emails_received' => 'int',
        'inboxes_created' => 'int',
        'storage_used' => 'int',
        'emails_deleted' => 'int',
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
        return disk_total_space(storage_path()) - disk_free_space(storage_path());
    }

    public static function emailsDeleted(): int
    {
        return self::sum('emails_deleted');
    }

    public static function storageUsedBetween($date1, $date2): int
    {
        return self::whereBetween('created_at', [$date1, $date2])->sum('storage_used');
    }
}
