<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Email extends Model
{
	public $incrementing = false;

	use Uuids;
}
