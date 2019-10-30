<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Sender extends Model
{
    use Uuids;
    
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['email', 'display_name'];
    protected $hidden = ['local_part', 'domain'];
}
