<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    use HasFactory;

    public static $QUARTERS = ['q1', 'q2', 'q3', 'q4'];

    public $timestamps = false;
    protected $guarded = ['id'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
