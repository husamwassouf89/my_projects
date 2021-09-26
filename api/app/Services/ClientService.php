<?php


namespace App\Services;


use App\Imports\ClientImport;
use App\Models\Client\Client;
use App\Models\Client\Grade;
use App\Models\Staging\Stage;
use App\Traits\IFRS9;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{
    use IFRS9;

    public function index($input)
    {
        $data = Client::allJoins()->selectIndex()->paginate($input['page_size']);
        return $this->handlePaginate($data, 'clients');
    }

    public function store($input)
    {
        Excel::import(new ClientImport(), $input['path']);
        return true;
    }

    public function show($id)
    {
        $client = Client::where('clients.id', $id)->joins()->selectShow()->firstOrFail();
        return $this->calculate($client);
    }

    private function calculate($client)
    {
        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $key => $info) {
                $info->class_type_id = $client->class_type_id;
                $info->irs_score     = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);
                if ($info->irs_score) {
                    $grade             = (new ClientIRSProfileService())
                        ->getClientGradeId($client->financial_data, $info->irs_score);
                    $info->grade       = Grade::where('class_type_id', $client->class_type_id)
                                              ->where('serial_no', $grade)->first()->name;
                    $info->final_grade = (new ClientIRSProfileService())
                        ->gradePastDueDays($grade, $account->past_due_days, $client->class_type_id);
                    $info->pd          = (new PDService())->getPdByYearQuarter($info->year, $info->quarter);
                    if ($info->pd) {
                        $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                    } else {
                        $info->pd = null;
                    }

                    $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $grade);
                    $stageModel     = Stage::where('serial_no', $stage)->first();
                    $info->stage    = $stageModel->name ?? null;
                    $info->stage_id = $stageModel->id ?? null;

                    $info      = $this->finalLGD($info);
                    $info->ecl = $info->pd * $info->ead * $info->lgd;
                } else {
                    $info->stage       = null;
                    $info->grade       = null;
                    $info->final_grade = null;
                    $info->pd          = null;
                }

            }
        }

        return $client;
    }


    public function showByCif($cif)
    {
        $client = Client::where('clients.cif', $cif)->joins()->selectShow()->firstOrFail();

        return $this->calculate($client);
    }

    public function getAccountClient($account)
    {
        return Client::join('client_accounts', 'client_accounts.client_id', '=', 'clients.id')
                     ->join('account_infos', 'client_accounts.id', 'account_infos.client_account_id')
                     ->where('account_infos.id', $account->id)
                     ->select('clients.*')
                     ->first();
    }


}
