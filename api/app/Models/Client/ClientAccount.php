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
        return $query
                  // ->join('account_infos', 'client_accounts.id', '=', 'account_infos.client_account_id')
                     ->join('types', 'types.id', '=', 'client_accounts.type_id')
                     ->join('currencies', 'currencies.id', '=', 'client_accounts.main_currency_id')
                     ->leftJoin('currencies as gu_currencies', 'gu_currencies.id', '=', 'client_accounts.guarantee_currency_id')
                     ->leftJoin('document_types', 'document_types.id', '=', 'client_accounts.document_type_id');
    }

    public function scopeSelectShow(Builder $query)
    {
        return $query->select('client_accounts.*', 'types.name as type_name', 'currencies.name as currency_name', 'gu_currencies.name as gu_currency_name', 'document_types.name as document_type',
                              'document_types.ccf as document_type_ccf');
    }

    public function accountInfos()
    {
        return $this->hasMany(AccountInfo::class);
    }

    public function mainCurrency()
    {
        return $this->belongsTo(Currency::class, 'main_currency_id');
    }

    public function guaranteeCurrency()
    {
        return $this->belongsTo(Currency::class, 'guarantee_currency_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
