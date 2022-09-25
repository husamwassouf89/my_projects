<?php

namespace App\Models\Client;

use App\Models\Attachment;
use App\Models\IRS\ClientIRSProfile;
use App\Models\Model;
use App\Models\Staging\ClientStagingProfile;
use App\Models\Staging\Stage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    public static $FINANCIAL_STATUS
        = ['None', 'Financial Data For Three Years', 'Financial Data For Two Years',
           'Financial Data For One Year', 'Without Financial Data'];
    public $timestamps = false;
    protected $casts = ['cif' => 'string'];
    protected $guarded = ['id'];

    public function scopeSelectShow($query, $year = null, $quarter = null)
    {
        return $query->select('clients.*', 'branches.name as branch_name', 'class_types.name as class_type_name')
                     ->with(['clientAccounts' => function ($query2) use ($year, $quarter) {
                         $query2->joins()->selectShow()->with(['accountInfos' => function ($query3) use ($year, $quarter) {
                             if ($year) {
                                 $query3->where('account_infos.year', $year);
                             }
                             if ($quarter) {
                                 $query3->where('account_infos.quarter', $quarter);
                             }
                             $query3->orderBy('account_infos.id', 'desc');
                             $query3->distinct('year', 'quarter');
                         }]);
                     }]);
    }

    public function scopeSelectIndex(Builder $query)
    {
        return $query->joins()->select('clients.*', 'branches.name as branch_name', 'class_types.name as class_type_name');
    }

    public function scopeSelectLimits(Builder $query)
    {
        return $query->joins()->join('limits', 'limits.client_id', '=', 'clients.id')
                     ->leftJoin('currencies as ldmc', 'limits.direct_limit_currency_id', '=', 'ldmc.id')
                     ->leftJoin('currencies as lundmc', 'limits.direct_limit_currency_id', '=', 'lundmc.id')
                     ->select('clients.*', 'branches.name as branch_name', 'class_types.name as class_type_name',
                              'limits.general_limit_lcy', 'limits.direct_limit_lcy', 'limits.un_direct_limit_lcy', 'limits.cancellable',
                              'ldmc.name as direct_limit_currency_name', 'lundmc.name as un_direct_limit_currency_name'
                     );
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
                     ->join('account_infos', 'account_infos.client_account_id', '=', 'client_accounts.id')
                     ->join('types', 'client_accounts.type_id', '=', 'types.id')
                     ->leftJoin('document_types', 'document_types.id', '=', 'client_accounts.document_type_id');
    }

    public function scopeIRSJoins(Builder $query, $filterType)
    {
        $query->leftJoin('branches', 'branches.id', '=', 'clients.branch_id');
        $query->join('class_types', 'class_types.id', '=', 'clients.class_type_id');
        $query->join('client_accounts', 'client_accounts.client_id', '=', 'clients.id');
        $query->join('account_infos', 'account_infos.client_account_id', '=', 'client_accounts.id');
        $query->join('types', 'client_accounts.type_id', '=', 'types.id');
        $query->leftJoin('document_types', 'document_types.id', '=', 'client_accounts.document_type_id');
        if ($filterType == 'with') {
            $query->join('client_i_r_s_profiles', 'clients.id', '=', 'client_i_r_s_profiles.client_id');
        } else if ($filterType == 'without') {
            $exclude = ClientIRSProfile::select('client_id')->get()->pluck('client_id')->toArray();
            if (count($exclude) > 0) {
                $query->whereNotIn('clients.id', $exclude);
            }
        }

        return $query;
    }

    public function scopeStageJoins(Builder $query, $filterType)
    {
        $query->leftJoin('branches', 'branches.id', '=', 'clients.branch_id');
        $query->join('class_types', 'class_types.id', '=', 'clients.class_type_id');
        $query->join('client_accounts', 'client_accounts.client_id', '=', 'clients.id');
        $query->join('account_infos', 'account_infos.client_account_id', '=', 'client_accounts.id');
        $query->join('types', 'client_accounts.type_id', '=', 'types.id');
        $query->leftJoin('document_types', 'document_types.id', '=', 'client_accounts.document_type_id');
//        $query->leftJoin('client_staging_profiles', 'clients.id', '=', 'client_staging_profiles.client_id');

        if ($filterType == 'with') {
            $query->join('client_staging_profiles', 'clients.id', '=', 'client_staging_profiles.client_id');
        } else if ($filterType == 'without') {
            $exclude = ClientStagingProfile::select('client_id')->get()->pluck('client_id')->toArray();
            if (count($exclude) > 0) {
                $query->whereNotIn('clients.id', $exclude);
            }
        }

        return $query;
    }


    public function clientAccounts()
    {
        return $this->hasMany(ClientAccount::class);
    }

    public function clientIRSProfiles()
    {
        return $this->hasMany(Client::class);
    }

    public function limits()
    {
        return $this->hasMany(Limit::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }
}
