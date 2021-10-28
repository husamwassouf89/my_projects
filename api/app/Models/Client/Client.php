<?php

namespace App\Models\Client;

use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    public static $FINANCIAL_STATUS = ['None', 'Sufficient financial data', 'Insufficient financial data', 'Without Financial Data'];

    public $timestamps = false;
    protected $guarded = ['id'];

    public function scopeSelectShow(Builder $query)
    {
        return $query->select('clients.*', 'branches.name as branch_name', 'class_types.name as class_type_name')
                     ->with(['clientAccounts' => function ($query2) {
                         $query2->joins()->selectShow()->with('accountInfos');
                     }]);
    }

    public function scopeSelectIndex(Builder $query)
    {
        return $query->select('clients.*', 'branches.name as branch_name', 'class_types.name as class_type_name', 'client_accounts.loan_key', 'types.name as type');
    }

    public function scopeJoins(Builder $query)
    {
        return $query->leftJoin('branches', 'branches.id', '=', 'clients.branch_id')
                     ->join('class_types', 'class_types.id', '=', 'clients.class_type_id');
    }

    public function scopeAllJoins(Builder $query)
    {
        return $query->leftJoin('branches', 'branches.id', '=', 'clients.branch_id')
                     ->join('class_types', 'class_types.id', '=', 'clients.class_type_id')
                     ->join('client_accounts', 'client_accounts.client_id', '=', 'clients.id')
                     ->join('types', 'client_accounts.type_id', '=', 'types.id');
    }

    public function clientAccounts()
    {
        return $this->hasMany(ClientAccount::class);
    }

    public function clientIRSProfiles()
    {
        return $this->hasMany(Client::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
