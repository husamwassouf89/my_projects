<?php

namespace App\Models\IRS;

use App\Models\Client\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientIRSProfile extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function answers()
    {
        return $this->hasMany(Answer::class)->selectShow();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
