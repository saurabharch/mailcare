<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    protected $fillable = [
        'id',
        'title',
        'sender',
        'inbox',
        'subject',
        'has_attachments',
        'action_url',
        'action_secret_token',
        'emails_received',
    ];
    protected $casts = ['has_attachments' => 'boolean'];
    public $incrementing = false;
    protected $keyType = 'string';
}
