<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    public static $OFF_BALANCE_DOCUMENTS = ['LC', 'LG', 'Document'];
    public $timestamps = false;
    protected $guarded = ['id'];
}
