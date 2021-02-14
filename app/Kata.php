<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kata extends Model
{
    protected $table = "kata";
    protected $primaryKey = "isi";
    public $incrementing = false;
    public $timestamps = false;
    protected $perPage = 45;
}
