<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Sender extends Model
{
    use Uuids;
    
    public $incrementing = false;
    protected $fillable = ['email', 'display_name', 'local_part', 'domain'];
}
