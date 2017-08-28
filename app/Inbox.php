<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
	protected $fillable = ['recipient'];
	
    public function emails()
    {
    	return $this->hasMany('App\Email');
    }
}
