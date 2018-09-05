<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use App\Traits\StorageForHuman;

class Attachment extends Model
{
    use Uuids;
    use StorageForHuman;
    
    public $incrementing = false;
    protected $appends = ['size_for_human'];

    public function hashHeaders($headers)
    {
        return md5(json_encode($headers));
    }

    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    public function getSizeForHumanAttribute()
    {
        return $this->humanFileSize($this->size_in_bytes);
    }
}
