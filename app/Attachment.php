<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Attachment extends Model
{
	public $incrementing = false;

    use Uuids;

    public function email()
    {
    	return $this->belongsTo(Email::class);
    }
}
