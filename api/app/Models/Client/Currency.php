<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public static $TYPES = ['foreign', 'local'];
    public $timestamps = false;
    protected $guarded = ['id'];
}
