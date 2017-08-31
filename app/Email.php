<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Carbon\Carbon;

class Email extends Model
{
	public $incrementing = false;

	use Uuids;

	public function inbox()
	{
		return $this->belongsTo('App\Inbox');
	}

    public function attachments()
    {
    	return $this->hasMany('App\Attachment');
    }

	public function path()
	{
		return 'emails/' . $this->created_at->format('Y/m/d/') . $this->id;
	}

	public function fullPath()
	{
		return storage_path('app/'.$this->path());
	}

    public function isUnread()
    {
        return empty($this->read);
    }

    public function read()
    {
        $this->read = Carbon::now();
        $this->save();
    }
}
