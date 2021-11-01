<?php


namespace App\Services;


use App\Imports\BankImport;
use App\Imports\ClientImport;
use App\Imports\DocumentImport;
use App\Models\Client\Client;
use App\Models\Client\DocumentType;
use App\Models\Client\Grade;
use App\Models\Client\Predefined;
use App\Models\Staging\Stage;
use App\Traits\IFRS9;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{
    // All the magic is here :D
    use IFRS9;

    public function index($input)
    {
        if (!isset($input['class_type_id']) or $input['class_type_id'] == null) $input['class_type_id'] = 1;
        $data = Client::query();
        $data->where('class_type_id', $input['class_type_id']);
        $data->allJoins()->selectIndex();
        if (isset($input['year']) and $input['year']) $data->where('year', $input['year']);
        if (isset($input['quarter']) and $input['quarter']) $data->where('quarter', $input['quarter']);
        if (isset($input['type']) and $input['type'] == 'documents') $data->whereIn('types.name', DocumentType::$OFF_BALANCE_DOCUMENTS);
        $data = $data->paginate($input['page_size']);
        return $this->handlePaginate($data, 'clients');
    }

    public function store($input)
    {
        if ($input['type'] == 'banks') {
            Excel::import(new BankImport(), $input['path']);
        } else if ($input['type'] == 'documents') {
            Excel::import(new DocumentImport(), $input['path']);
        } else {
            Excel::import(new ClientImport(), $input['path']);
        }
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
                    $info->grade_id    = $grade;

                    if (!$info->pd) {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter);
                        if ($info->pd) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }

                    }

                    $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $grade, $info);
                    $stageModel     = Stage::where('serial_no', $stage)->first();
                    $info->stage    = $stageModel->name ?? null;
                    $info->stage_no = $stageModel->serial_no ?? null;
                    $info->stage_id = $stageModel->id ?? null;

                    $info = $this->finalLGD($info);
                    $info = $this->eclCalc($info);
                } else if ($client->grade_id) {
                    $info->grade_id    = $client->grade_id;
                    $grade             = $client->grade->serial_no;
                    $info->final_grade = $client->grade->name;

                    // To handle central bank pd value
                    if ($client->class_type_id == 9) {

                        if ($account->mainCurrency->type == 'local') {
                            $type = 'local';

                        } else {
                            $type = 'foreign';
                        }
                        $value = Predefined::where('grade_id', $account->grade_id)->where('class_type_id', $client->class_type_id)
                                           ->where('stage_id', $account->stage_id)
                                           ->where('currency_type', $type)
                                           ->first();
                        if ($value) {
                            $info->pd  = $value->pd;
                            $info->lgd = $value->lgd;
                        } else {
                            $info->pd = null;
                        }
                    } else {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter);
                        if ($info->pd and $info->pd != -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }
                    }


                    $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade);
                    $stageModel     = Stage::where('serial_no', $stage)->first();
                    $info->stage    = $stageModel->name ?? null;
                    $info->stage_no = $stageModel->serial_no ?? null;
                    $info->stage_id = $stageModel->id ?? null;

                    $info = $this->finalLGD($info, $client->class_type_id);
                    $info = $this->eclCalc($info);
                } else {
                    $info->stage       = null;
                    $info->grade       = null;
                    $info->final_grade = null;
                    $info->pd          = null;
                    $info->ecl         = null;
                    $info->lgd_null    = null;
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

    public function changeFinancialStatus($input)
    {
        return Client::where('id', $input['id'])->update(['financial_status' => $input['financial_status']]);
    }

    public function getPredefinedValue($classTypeId, $gradeId, $stageId, $type)
    {
        $preDefined = Predefined::where('class_type_id', $classTypeId)
                                ->where('grade_id', $gradeId)->where('stage_id', $stageId)
                                ->first();
        if ($preDefined) {
            if ($type == 'lgd') return $preDefined->lgd;
            else if ($type == 'pd') return $preDefined->pd;
            else return -1;
        }

        return -1;
    }


}
