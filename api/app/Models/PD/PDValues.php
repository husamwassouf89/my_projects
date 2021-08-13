<?php

namespace App\Models\PD;

use App\Models\Client\Grade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PDValues extends Model
{
    use HasFactory;

    public    $timestamps = false;
    protected $guarded    = ['id'];

    public function row()
    {
        return $this->belongsTo(Grade::class, 'row_id');
    }

    public function column()
    {
        return $this->belongsTo(Grade::class, 'column_id');
    }
}
