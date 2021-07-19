<?php

namespace App\Models\PD;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PD extends Model
{
    use HasFactory;

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function getPathAttribute($value)
    {
        return asset($value, env('HTTPS', false));
    }
}
