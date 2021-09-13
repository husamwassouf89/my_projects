<?php


namespace App\Services;


use App\Imports\ClientImport;
use App\Models\Client\Client;
use App\Models\Client\Grade;
use App\Models\Staging\Stage;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{

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
        $client = Client::where('clients.id', $id)->joins()->selectShow()->first();

        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                $info->irs_score   = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);
                $grade             = (new ClientIRSProfileService())
                    ->getClientGradeId($client->financial_data, $info->irs_score);
                $info->grade       = Grade::where('class_type_id', $client->class_type_id)
                                          ->where('serial_no', $grade)->first()->name;
                $info->final_grade = (new ClientIRSProfileService())
                    ->gradePastDueDays($grade, $account->past_due_days, $client->class_type_id);
                $info->pd          = (new PDService())->getPdByYearQuarter($info->year, $info->quarter)['final_calibrated_used_PD'][$grade];

                $stage = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $grade);

                $info->stage = Stage::where('serial_no', $stage)->first()->name??null;

                $info->ead = 47730188;
                $info->lgd = 4773018.79;
                $info->ecl = 407139;
            }
        }
        return $client;
    }

    public function showByCif($cif)
    {
        $client =  Client::where('clients.cif', $cif)->joins()->selectShow()->first();
        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                $info->irs_score   = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);
                $grade             = (new ClientIRSProfileService())
                    ->getClientGradeId($client->financial_data, $info->irs_score);
                $info->grade       = Grade::where('class_type_id', $client->class_type_id)
                                          ->where('serial_no', $grade)->first()->name;
                $info->final_grade = (new ClientIRSProfileService())
                    ->gradePastDueDays($grade, $account->past_due_days, $client->class_type_id);
                $info->pd          = (new PDService())->getPdByYearQuarter($info->year, $info->quarter)['final_calibrated_used_PD'][$grade];

                $stage = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $grade);

                $info->stage = Stage::where('serial_no', $stage)->first()->name??null;

                $info->ead = 47730188;
                $info->lgd = 4773018.79;
                $info->ecl = 407139;
            }
        }
        return $client;
    }

    public function getAccountClient($account)
    {
        return Client::join('client_accounts', 'client_accounts.client_id', '=', 'clients.id')
                     ->join('account_infos', 'client_accounts.id', 'account_infos.client_account_id')
                     ->where('account_infos.id', $account->id)
                     ->select('clients.*')
                     ->first();
    }

    private function calculateLGD($account)
    {

    }


    private function calculateEAD($account)
    {
        $outstanding     = $account->outstanding_lcy ?: 0;
        $accruedInterest = $account->accrued_interest_lcy ?: 0;
        $accruedInterest = $account->accrued_interest_lcy ?: 0;
    }

}
