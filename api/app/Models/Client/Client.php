<?php

namespace App\Models\Client;

use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    public function scopeSelectIndex(Builder $query)
    {
        // TODO: Implement scopeSelectIndex() method.
    }

    public function scopeSelectShow(Builder $query)
    {
        // TODO: Implement scopeSelectShow() method.
    }

    public function scopeJoins(Builder $query)
    {
        // TODO: Implement scopeJoins() method.
    }
}
