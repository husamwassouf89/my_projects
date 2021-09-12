<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = ['id'];
}
