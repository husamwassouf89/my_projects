<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingOption extends Model
{
    use HasFactory;

    public static $TYPES = ['Yes', 'No', 'Others', 'Linked'];
    public $timestamps = false;
    public $guarded = ['id'];

    public function results(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StagingOptionResult::class);
    }


}
