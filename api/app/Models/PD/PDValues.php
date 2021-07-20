<?php

namespace App\Models\PD;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PDValues extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];
}
