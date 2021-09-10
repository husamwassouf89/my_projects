<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingQuestion extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];

    public function options()
    {
        return $this->hasMany(StagingOption::class);
    }

}
