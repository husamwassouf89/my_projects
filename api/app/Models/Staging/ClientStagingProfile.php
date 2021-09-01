<?php

namespace App\Models\Staging;

use App\Models\Client\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientStagingProfile extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function answers()
    {
        return $this->hasMany(StagingAnswer::class)->selectShow();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
