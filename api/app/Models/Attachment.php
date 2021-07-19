<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    public function attachmentable()
    {
        return $this->morphTo();
    }

    public function getPathAttribute($value)
    {
        if ($this->attributes['path'] == 'file') {
            return asset($value, env('HTTPS', false));
        } else {
            return $value;
        }
    }
}
