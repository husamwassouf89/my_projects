<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Predefined extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    public function classType(){
        return $this->belongsTo(ClassType::class);
    }
}
