<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountInfo extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['updated_at'];
}
