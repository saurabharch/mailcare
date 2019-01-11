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
        'action_delete_email',
        'emails_received',
    ];
    protected $casts = [
        'emails_received' => 'int',
        'has_attachments' => 'boolean',
        'action_delete_email' => 'boolean',
    ];
    public $incrementing = false;
    protected $keyType = 'string';
}
