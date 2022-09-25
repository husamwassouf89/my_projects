<?php

namespace App\Models\IRS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded    = ['id'];

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function irs()
    {
        return $this->belongsTo(IRS::class,'irs_id');
    }
}
