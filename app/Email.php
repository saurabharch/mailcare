<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Email extends Model
{
	public $incrementing = false;

	use Uuids;

	public function path()
	{
		return 'emails/' . $this->created_at->format('Y/m/d/') . $this->id;
	}

	public function fullPath()
	{
		return storage_path('app/'.$this->path());
	}
}
