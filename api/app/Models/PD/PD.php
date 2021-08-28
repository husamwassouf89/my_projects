<?php

namespace App\Models\PD;

use App\Models\Attachment;
use App\Models\Client\ClassType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PD extends Model
{

    protected $hidden = ['updated_at'];
    use HasFactory;

    public function scopeSelectIndex($query)
    {
        return $query->joins()->select('p_d_s.*', 'class_types.name as class_type_name');
    }

    public function scopeSelectShow($query)
    {
        return $query->select('p_d_s.*', 'class_types.name as class_type_name')
                     ->with('attachments', 'values');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function values()
    {
        return $this->hasMany(PDValues::class);
//                    ->join('grades as rows', 'row_id', '=', 'rows.id')
//                    ->join('grades as columns', 'column_id', '=', 'columns.id')
//                    ->select('p_d_values.*', 'rows.name as row', 'columns.name as column');
    }

    public function getPathAttribute($value)
    {
        return asset($value, env('HTTPS', false));
    }

    public function classType()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function scopeJoins($query)
    {
        return $query->join('class_types', 'class_types.id', '=', 'p_d_s.class_type_id');
    }

}
