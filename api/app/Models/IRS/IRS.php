<?php

namespace App\Models\IRS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IRS extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded    = ['id'];

    public function scopeJoins($query)
    {
        return $query->join('categories', 'categories.id', '=', 'i_r_s.category_id')
                     ->join('class_types', 'class_types.id', '=', 'i_r_s.class_type_id');
    }

    public function scopeSelectIndex($query)
    {
        return $query->select('i_r_s.*', 'categories.name as category_name',
                              'class_types.name as class_type_name');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'irs_id')->with('options');
    }
}
