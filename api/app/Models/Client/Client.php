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
        return $query->select('clients.*', 'branches.name as branch_name', 'class_types.name as class_type_name')
                     ->with(['clientAccounts' => function ($query2) {
                         $query2->joins()->selectShow();
                     }]);
    }

    public function scopeJoins(Builder $query)
    {
        return $query->join('branches', 'branches.id', '=', 'clients.branch_id')
                     ->join('class_types', 'class_types.id', '=', 'clients.class_type_id');
    }

    public function clientAccounts()
    {
        return $this->hasMany(ClientAccount::class);
    }
}
