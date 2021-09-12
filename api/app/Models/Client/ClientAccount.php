<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAccount extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function scopeJoins(Builder $query)
    {
        return $query->join('types', 'types.id', '=', 'client_accounts.type_id')
                     ->join('currencies', 'currencies.id', '=', 'client_accounts.main_currency_id');
    }

    public function scopeSelectShow(Builder $query)
    {
        return $query->select('client_accounts.*', 'types.name as type_name', 'currencies.name as currency_name');
    }

    public function accountInfos()
    {
        return $this->hasMany(AccountInfo::class);
    }

}
