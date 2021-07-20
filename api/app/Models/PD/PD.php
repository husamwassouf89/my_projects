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

    public function scopeIndexSelect($query)
    {
        return $query->with('attachments');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function values()
    {
        return $this->hasMany(PDValues::class);
    }

    public function getPathAttribute($value)
    {
        return asset($value, env('HTTPS', false));
    }

    public function classType()
    {
        return $this->belongsTo(ClassType::class);
    }
}
