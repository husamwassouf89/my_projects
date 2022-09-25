<?php

namespace App\Models\Client;

use App\Models\Staging\StagingQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    use HasFactory;

    public static $QUARTERS = ['q1', 'q2', 'q3', 'q4'];
    public $timestamps = false;
    protected $guarded = ['id'];

    public static function getYears()
    {
        $allYears = [];
        for ($i = 2018; $i <= Date('Y'); $i++) {
            array_push($allYears, $i);
        }
        return $allYears;
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function stagingQuestions()
    {
        return $this->hasMany(StagingQuestion::class);
    }
}
