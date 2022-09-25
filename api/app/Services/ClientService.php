<?php


namespace App\Services;


use App\Imports\BankImport;
use App\Imports\ClientImport;
use App\Imports\DocumentImport;
use App\Models\Attachment;
use App\Models\Client\AccountInfo;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\ClientAccount;
use App\Models\Client\DocumentType;
use App\Models\Client\Grade;
use App\Models\Client\Limit;
use App\Models\Client\Predefined;
use App\Models\Staging\ClientStagingProfile;
use App\Models\Staging\Stage;
use App\Models\Value;
use App\Traits\IFRS9;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{
    // All the magic is here :D
    use IFRS9;


    public function index($input)
    {
//        if (!isset($input['class_type_category']) or $input['class_type_category'] == null) $input['class_type_category'] = 'facility';
        $data = Client::query();
        $data->allJoins();

        if (isset($input['class_type_id']) and $input['class_type_id'])
            $data->where('class_type_id', $input['class_type_id']);
        if (isset($input['year']) and $input['year']) $data->where('account_infos.year', $input['year']);

        if (isset($input['quarter']) and $input['quarter']) $data->where('account_infos.quarter', $input['quarter']);

        // Class category filter (facility, financial)
        if (isset($input['class_type_category']))
            $data->where('class_types.category', $input['class_type_category']);
        // Off-Balance
        if (isset($input['type']) and $input['type'] == 'documents') $data->whereIn('types.name', DocumentType::$OFF_BALANCE_DOCUMENTS);
        else $data->whereNotIn('types.name', DocumentType::$OFF_BALANCE_DOCUMENTS);
        // Limits Filter
        if (isset($input['limit']) and $input['limit'] == 'yes') {
            $data->join('limits', 'limits.client_id', '=', 'clients.id');
            if (isset($input['year']) and $input['year']) $data->where('limits.year', $input['year']);
            if (isset($input['quarter']) and $input['quarter']) $data->where('limits.quarter', $input['quarter']);
        }

        $ids  = $data->get()->pluck('client_id')->toArray();
        $data = Client::query();
        $data = $data->whereIn('clients.id', $ids);
//        if (isset($input['limits']) and $input['limits'] == 'yes') {
//            $data->selectLimits();
//        } else {
//            $data->selectIndex();
//        }
        $data->selectIndex();
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

    public function show($id, $balance = 'on', $quarter = null, $year = null, $limit = null)
    {
        $client              = Client::where('clients.id', $id)->joins()->selectShow()->firstOrFail();
        $att                 = Attachment::where('attachmentable_id', $client->id)->get();
        $client->attachments = $att;

        if ($limit == 'yes') {
            $client = $this->addUsedLimits($client, $year, $quarter, $balance);
        }

        return $this->calculate($client, $balance, $limit);
    }

    public function addUsedLimits($client, $year, $quarter, $balance)
    {
        $direct   = 0; // balance is on
        $undirect = 0; // balance is off
        $list     = [];
        foreach ($client->clientAccounts as $key0 => $account) {
            foreach ($account->accountInfos as $key => $info) {
                if (in_array($account->type_name, DocumentType::$ON_BALANCE_DOCUMENTS)) {
                    $direct += $info->outstanding_lcy;
                }
                if (in_array($account->type_name, DocumentType::$OFF_BALANCE_DOCUMENTS)) {
                    array_push($list, $info->outstanding_lcy);
                    $undirect += $info->outstanding_lcy;
                }
                break;
            }

        }
        $client->used_direct_limit    = $direct;
        $client->used_un_direct_limit = $undirect;

        $userLimit = Limit::where('client_id', $client->id)->where('year', $year)->where('quarter', $quarter)->first();
        if ($userLimit) {
            if ($userLimit->cancellable == 'yes') {
                $document = DocumentType::find(3);
            } else {
                $document = DocumentType::find(4);
            }
            if ($document) {
                $ccf = $document->ccf;
            }
        } else $ccf = 1;

        $client->general_limit         = $userLimit->general_limit_lcy ?? 0;
        $client->direct_limit          = $userLimit->direct_limit_lcy ?? 0;
        $client->un_direct_limit       = $userLimit->un_direct_limit_lcy ?? 0;
        $client->unused_direct_limit   = max($client->direct_limit - $client->used_direct_limit, 0);
        $client->unused_undirect_limit = max($client->un_direct_limit - $client->used_un_direct_limit, 0);
        $client->ccf                   = $ccf;
        $client->ead                   = $client->ead * $client->ccf;

        $ok  = false;
        $ok2 = false;
        foreach ($client->clientAccounts as $key => $account) {
            foreach ($account->accountInfos as $key2 => $info) {
                if (!$ok) {
                    $account = AccountInfo::find($info->id);
                    $ok      = true;
                } else {
                    unset($account->accountInfos[$key2]);
                }
            }
            if ($ok2) {
                unset($client->clientAccounts[$key]);
            } else {
                if ($balance == 'on') {
                    $client->clientAccounts[$key]->main_currency_id = $userLimit->direct_limit_currency_id;
                } else {
                    $client->clientAccounts[$key]->main_currency_id = $userLimit->un_direct_limit_currency_id;
                }
            }
            $ok2 = true;
        }

        return $client;
    }

    private function calculateOnBalance($client, $balance = 'on', $limit = null, $hcm = 0.9)
    {
        foreach ($client->clientAccounts as $key0 => $account) {
//          Hair-cut
            if (count($account->accountInfos) <= 0) {
                unset($client->clientAccounts[$key0]);
            }
            $account->hcm = $hcm;
            if (!$account->type_name) $ok = false;
            else $ok = in_array($account->type_name, DocumentType::$OFF_BALANCE_DOCUMENTS);
//          Remove off-balance accounts
            if ($ok) {
                unset($client->clientAccounts[$key0]);
                continue;
            }
            foreach ($account->accountInfos as $key => $info) {
                $info->client_id     = $client->id;
                $info->hcm           = $account->hcm;
                $info->class_type_id = $client->class_type_id;
                $classType           = ClassType::find($info->class_type_id);

                $info->irs_score = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);

                if ($client->grade_id) {
//                    dd($client->grade_id);
                    $info->grade_id    = $client->grade_id;
                    $grade             = $client->grade->serial_no;
                    $info->final_grade = $client->grade->name;

                    // To handle central bank pd value
                    if ($classType->sub_category == 'central bank') {

                        if ($account->mainCurrency->type == 'local') {
                            $type = 'local';

                        } else {
                            $type = 'foreign';
                        }
//                        dd($client->grade_id,$client->stage_id,$client->class_type_id);
                        $value = Predefined::where('grade_id', $client->grade_id)->where('class_type_id', $client->class_type_id)
                                           ->where('stage_id', $client->stage_id)
                                           ->where('currency_type', $type)
                                           ->first();
//                        dd($value);
                        if ($value) {
                            $info->pd  = $value->pd;
                            $info->lgd = $value->lgd;
                        } else {
                            $info->pd = null;
                        }
                    } else {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd != -1) {
//                            dd($info->pd['final_calibrated_used_PD']);
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }
                    }


//                    $info = $this->finalLGD($info, $client->class_type_id, $limit, $client);
//                    $info = $this->eclCalc($info);

                } else if ($info->irs_score or $classType->sub_category == 'retail') {

                    if ($info->irs_score) {

                        $grade = (new ClientIRSProfileService())
                            ->getClientGradeId($client->financial_status, $info->irs_score);

                        $info->grade = Grade::where('class_type_id', $client->class_type_id)
                                            ->where('serial_no', $grade)->first()->name ?? 'WRONG SCORE VALUE';

                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays($grade, $info->past_due_days, $client->class_type_id);
                        $info->grade_id    = $grade;

                        $grade = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)->first();
                        $grade = $grade->serial_no;

                    } else if ($classType->sub_category == 'retail') {
                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays(0, $info->past_due_days, $client->class_type_id);

                        if ($info->final_grade) {
                            $grade          = Grade::where('class_type_id', $client->class_type_id)
                                                   ->where('name', $info->final_grade)
                                                   ->first();
                            $info->grade_id = $grade->id;
                            $info->grade    = $grade->name;
                            $grade          = $grade->serial_no;
                        }

                    }

                    if ($info->stage_id == 3) {
                        $info->pd = 1;
                    }

                    if (!$info->pd) {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd > -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }

                    }

                } else {
                    $info = $this->fillNull($info);
                }
                if ($classType->sub_category == 'retail' and $info->final_grade) {
                    // in retail stage is equal to grade
                    $info->stage_id   = $grade + 1;
                    $client->stage_id = $grade + 1;
                }

                if ($info->stage_id) {
                    $stageModel     = Stage::where('id', $info->stage_id)->first();
                    $info->stage    = $stageModel->name ?? null;
                    $info->stage_no = $stageModel->serial_no ?? null;
                    $info->stage_id = $stageModel->id ?? null;
                } else {
//                    dd(1);
                    $stage = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade, $info);
                    if ($stage) {
                        // dd($stage);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
//                        dd($stageModel);

                    }
                }
                $client->final_grade = $info->final_grade;
                $client->stage       = $info->stage;

                $account->accountInfos[$key] = $this->ead($info);
            }

        }
        $eadSum = 0;
        foreach ($client->clientAccounts as $key0 => $account) {
            foreach ($account->accountInfos as $key => $info) {
                $eadSum += $info->ead;
            }
        }
        foreach ($client->clientAccounts as $key0 => $account) {
            foreach ($account->accountInfos as $key => $info) {
                $info->ead_sum = $eadSum;
                $info          = $this->finalLGD($info, $client->class_type_id, $limit, $balance);
                $info          = $this->eclCalc($info);
            }
        }

        return $client;
    }

    private function calculateOffBalance($client, $clientOn, $balance = 'on', $limit = null, $hcm = 0.9)
    {
        foreach ($client->clientAccounts as $key0 => $account) {
//          Hair-cut
            if (count($account->accountInfos) <= 0) {
                unset($client->clientAccounts[$key0]);
            }
            $account->hcm = $hcm;
            if (!$account->type_name) $ok = false;
            else $ok = in_array($account->type_name, DocumentType::$OFF_BALANCE_DOCUMENTS);

//          Remove on-balance accounts
            if (!$ok) {
                unset($client->clientAccounts[$key0]);
                continue;
            }

            foreach ($account->accountInfos as $key => $info) {
                $info->client_id     = $client->id;
                $info->hcm           = $account->hcm;
                $info->class_type_id = $client->class_type_id;
                $classType           = ClassType::find($info->class_type_id);

                $info->irs_score = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);

                if ($client->grade_id) {
                    $info->grade_id    = $client->grade_id;
                    $grade             = $client->grade->serial_no;
                    $info->final_grade = $client->grade->name;

                    // To handle central bank pd value
                    if ($classType->sub_category == 'central bank') {

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
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd != -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }
                    }

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }

                } else if ($info->irs_score or $classType->sub_category == 'retail') {

                    if ($info->irs_score) {

                        $grade = (new ClientIRSProfileService())
                            ->getClientGradeId($client->financial_status, $info->irs_score);

                        $info->grade = Grade::where('class_type_id', $client->class_type_id)
                                            ->where('serial_no', $grade)->first()->name ?? 'WRONG SCORE VALUE';

                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays($grade, $info->past_due_days, $client->class_type_id);
                        $info->grade_id    = $grade;

                        $grade = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)->first();
                        $grade = $grade->serial_no;

                    } else if ($classType->sub_category == 'retail') {
                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays(0, $info->past_due_days, $client->class_type_id);

                        if ($info->final_grade) {
                            $grade          = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)
                                                   ->first();
                            $info->grade_id = $grade->id;
                            $info->grade    = $grade->name;
                            $grade          = $grade->serial_no;
                        }


                    }


                    if ($info->stage_id == 3) {
                        $info->pd = 1;
                    }

                    if (!$info->pd) {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd > -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }

                    }


                    if ($classType->sub_category == 'retail' and $info->final_grade) {
                        // in retail stage is equal to grade
                        $info->stage_id = $info->grade;
                    }

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade, $info);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }

                } else {
                    $info = $this->fillNull($info);
                }
                $client->final_grade         = $info->final_grade;
                $client->stage               = $info->stage;
                $account->accountInfos[$key] = $this->ead($info);
            }

        }
        $eadSum = 0;
        foreach ($client->clientAccounts as $key0 => $account) {
            foreach ($account->accountInfos as $key => $info) {
                $eadSum += $info->ead;
            }
        }
        // Calculate the remaining of the guarantee Sec and Re (Cash Margin is imported via off-balance excel document)
        $sumCoveredBySec = 0;
        $sumCoveredByRe  = 0;
        $re              = 0;
        $sec             = 0;
        foreach ($clientOn->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                $sumCoveredBySec += $info->covered_by_sec;
                $sumCoveredByRe  += $info->covered_by_re;
                $re              = $info->pv_re_guarantees;
                $sec             = $info->pv_securities_guarantees;
            }
        }

        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $key => $info) {
                $account->accountInfos[$key]->pv_securities_guarantees = $sec - $sumCoveredBySec;
                $account->accountInfos[$key]->pv_re_guarantees         = $re - $sumCoveredByRe;
            }
        }

        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                $info->ead_sum = $eadSum;
                $info          = $this->finalLGD($info, $client->class_type_id, $limit, $balance);
                $info          = $this->eclCalc($info);
            }
        }
        return $client;
    }

    private function calculateLimitDirectBalance($client, $clientOn, $clientOff, $balance = 'on', $limit = null, $hcm = 0.9)
    {
        foreach ($client->clientAccounts as $account) {
//          Hair-cut
            if (count($account->accountInfos) <= 0) {
                unset($client->clientAccounts[$key0]);
            }
            $account->hcm = $hcm;
            foreach ($account->accountInfos as $key => $info) {
                $info->client_id     = $client->id;
                $info->hcm           = $account->hcm;
                $info->class_type_id = $client->class_type_id;
                $classType           = ClassType::find($info->class_type_id);

                $info->irs_score = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);

                if ($client->grade_id) {
                    $info->grade_id    = $client->grade_id;
                    $grade             = $client->grade->serial_no;
                    $info->final_grade = $client->grade->name;

                    // To handle central bank pd value
                    if ($classType->sub_category == 'central bank') {

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
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd != -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }
                    }

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }

                } else if ($info->irs_score or $classType->sub_category == 'retail') {

                    if ($info->irs_score) {

                        $grade = (new ClientIRSProfileService())
                            ->getClientGradeId($client->financial_status, $info->irs_score);

                        $info->grade = Grade::where('class_type_id', $client->class_type_id)
                                            ->where('serial_no', $grade)->first()->name ?? 'WRONG SCORE VALUE';

                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays($grade, $info->past_due_days, $client->class_type_id);
                        $info->grade_id    = $grade;

                        $grade = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)->first();
                        $grade = $grade->serial_no;

                    } else if ($classType->sub_category == 'retail') {
                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays(0, $info->past_due_days, $client->class_type_id);

                        if ($info->final_grade) {
                            $grade          = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)
                                                   ->first();
                            $info->grade_id = $grade->id;
                            $info->grade    = $grade->name;
                            $grade          = $grade->serial_no;
                        }


                    }


                    if ($info->stage_id == 3) {
                        $info->pd = 1;
                    }

                    if (!$info->pd) {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd > -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }

                    }


                    if ($classType->sub_category == 'retail' and $info->final_grade) {
                        // in retail stage is equal to grade
                        $info->stage_id = $info->grade;
                    }

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade, $info);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }

                } else {
                    $info = $this->fillNull($info);
                }
                $client->final_grade              = $info->final_grade;
                $client->stage                    = $info->stage;
                $account->accountInfos[$key]->ead = $client->ead;
            }

        }
        $eadSum = 0;
        foreach ($client->clientAccounts as $key0 => $account) {
            foreach ($account->accountInfos as $key => $info) {
                $eadSum += $info->ead;
            }
        }
        $list = [];
        // Calculate the remaining of the guarantee Sec and Re (Cash Margin is imported via off-balance excel document)
        $sumCoveredByCm  = 0;
        $sumCoveredBySec = 0;
        $sumCoveredByRe  = 0;
        $re              = 0;
        $sec             = 0;
        $cm              = 0;
        foreach ($clientOn->clientAccounts as $account1) {
            if (in_array($account1->type_name, DocumentType::$OFF_BALANCE_DOCUMENTS)) {
                continue;
            }

            foreach ($account1->accountInfos as $info1) {
                $sumCoveredByCm  += $info1->covered_by_cm;
                $sumCoveredBySec += $info1->covered_by_sec;
                $sumCoveredByRe  += $info1->covered_by_re;

                $re  = $info1->pv_re_guarantees;
                $sec = $info1->pv_securities_guarantees;
                $cm  += $info1->cm_guarantee;
            }
        }
        $sumCoveredBySecOff = 0;
        $sumCoveredByReOff  = 0;
        $list               = [];
        foreach ($clientOff->clientAccounts as $account2) {
            if (in_array($account2->type_name, DocumentType::$ON_BALANCE_DOCUMENTS)) {
                continue;
            }
            foreach ($account2->accountInfos as $info2) {
                $sumCoveredBySecOff += $info2->covered_by_sec;
                $sumCoveredByReOff  += $info2->covered_by_re;
                array_push($list, $info2->covered_by_re);
                break;
            }
        }
        foreach ($client->clientAccounts as $account3) {
            foreach ($account3->accountInfos as $key => $info3) {
                $account->accountInfos[$key]->cm_guarantee             = 0;
                $account->accountInfos[$key]->pv_securities_guarantees = ($sec - $sumCoveredBySec - $sumCoveredBySecOff);
                $account->accountInfos[$key]->pv_re_guarantees         = ($re - $sumCoveredByRe - $sumCoveredByReOff);
            }
        }

        foreach ($client->clientAccounts as $key4 => $account) {
            foreach ($account->accountInfos as $info) {
                $info->ead_sum = $eadSum;
                $info          = $this->finalLGD($info, $client->class_type_id, $limit, $balance);
                $info          = $this->eclCalc($info, $limit);
            }
            $client->clientAccounts[$key4]->loan_key  = 'Limit direct Account';
            $client->clientAccounts[$key4]->type_name = '';
        }

        return $client;
    }

    private function calculateLimitUnDirectBalance($client, $clientOn, $clientOff, $clientLimitDirect, $balance = 'on', $limit = null, $hcm = 0.9)
    {

        foreach ($client->clientAccounts as $key0 => $account) {
            if (count($account->accountInfos) <= 0) {
                unset($client->clientAccounts[$key0]);
            }
//          Hair-cut
            $account->hcm = $hcm;
            foreach ($account->accountInfos as $key => $info) {
                $info->client_id     = $client->id;
                $info->hcm           = $account->hcm;
                $info->class_type_id = $client->class_type_id;
                $classType           = ClassType::find($info->class_type_id);

                $info->irs_score = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);

                if ($client->grade_id) {
                    $info->grade_id    = $client->grade_id;
                    $grade             = $client->grade->serial_no;
                    $info->final_grade = $client->grade->name;

                    // To handle central bank pd value
                    if ($classType->sub_category == 'central bank') {

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
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd != -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }
                    }

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }

                } else if ($info->irs_score or $classType->sub_category == 'retail') {

                    if ($info->irs_score) {
                        $grade             = (new ClientIRSProfileService())
                            ->getClientGradeId($client->financial_status, $info->irs_score);
                        $info->grade       = Grade::where('class_type_id', $client->class_type_id)
                                                  ->where('serial_no', $grade)->first()->name ?? 'WRONG SCORE VALUE';
                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays($grade, $info->past_due_days, $client->class_type_id);
                        $info->grade_id    = $grade;

                        $grade = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)->first();
                        $grade = $grade->serial_no;

                    } else if ($classType->sub_category == 'retail') {
                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays(0, $info->past_due_days, $client->class_type_id);

                        if ($info->final_grade) {
                            $grade          = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)
                                                   ->first();
                            $info->grade_id = $grade->id;
                            $info->grade    = $grade->name;
                            $grade          = $grade->serial_no;
                        }


                    }


                    if ($info->stage_id == 3) {
                        $info->pd = 1;
                    }

                    if (!$info->pd) {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd > -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }

                    }
                    if ($classType->sub_category == 'retail' and $info->final_grade) {
                        // in retail stage is equal to grade
                        $info->stage_id = $info->grade;
                    }

                    if ($info->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade, $info);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
//                        dd(1);
                        $info->stage_id = $stageModel->id ?? null;
                    }

                } else {
                    $info = $this->fillNull($info);
                }
                $client->final_grade              = $info->final_grade;
                $client->stage                    = $info->stage;
                $account->accountInfos[$key]->ead = $client->ead;
            }

        }
        $eadSum = 0;
        foreach ($client->clientAccounts as $key0 => $account) {
            foreach ($account->accountInfos as $key => $info) {
                $eadSum += $info->ead;
            }
        }
        // Calculate the remaining of the guarantee Sec and Re (Cash Margin is imported via off-balance excel document)
        $sumCoveredByCm  = 0;
        $sumCoveredBySec = 0;
        $sumCoveredByRe  = 0;
        $re              = 0;
        $sec             = 0;
        foreach ($clientOn->clientAccounts as $account1) {
            foreach ($account1->accountInfos as $info1) {
                $sumCoveredByCm  += $info1->covered_by_cm;
                $sumCoveredBySec += $info1->covered_by_sec;
                $sumCoveredByRe  += $info1->covered_by_re;
                $re              = $info1->pv_re_guarantees;
                $sec             = $info1->pv_securities_guarantees;
            }
        }
        // Calculate the remaining of the guarantee Sec and Re (Cash Margin is imported via off-balance excel document)
        $sumCoveredBySecOff = 0;
        $list               = [];
        $sumCoveredByReOff  = 0;
        foreach ($clientOff->clientAccounts as $account2) {
            foreach ($account2->accountInfos as $info2) {
                $sumCoveredBySecOff += $info2->covered_by_sec;
                $sumCoveredByReOff  += $info2->covered_by_re;
                array_push($list, $info2->covered_by_re);
                break;
            }
        }

        // Calculate the remaining of the guarantee Sec and Re (Cash Margin is imported via off-balance excel document)
        $sumCoveredBySecLimitDirect = 0;
        $sumCoveredByReLimitDirect  = 0;
        $list                       = [];
        foreach ($clientLimitDirect->clientAccounts as $account3) {
            foreach ($account3->accountInfos as $info3) {
                $sumCoveredBySecLimitDirect += $info3->covered_by_sec;
                $sumCoveredByReLimitDirect  += $info3->covered_by_re;
                array_push($list, $info3->covered_by_re);
//                break;
            }
        }
        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $key => $info) {
                $account->accountInfos[$key]->cm_guarantee             = 0;
                $account->accountInfos[$key]->pv_securities_guarantees = ($sec - $sumCoveredBySec - $sumCoveredBySecOff - $sumCoveredBySecLimitDirect);
                $account->accountInfos[$key]->pv_re_guarantees         = ($re - $sumCoveredByRe - $sumCoveredByReOff - $sumCoveredByReLimitDirect);
            }
        }

        foreach ($client->clientAccounts as $key4 => $account) {
            foreach ($account->accountInfos as $info) {
                $info->ead_sum = $eadSum;
                $info          = $this->finalLGD($info, $client->class_type_id, $limit, $balance);
                $info          = $this->eclCalc($info, $limit);
            }
            $client->clientAccounts[$key4]->loan_key  = 'Limit Indirect Account';
            $client->clientAccounts[$key4]->type_name = '';

        }

        return $client;
    }

    private function calculate($clientOn, $clientOff, $clientLimitDirect, $clientLimitUnDirect, $balance = 'on', $limit = null)
    {
        $hcm = Value::find(7);
        if (!$hcm) $hcm = 0.9;
        else $hcm = 1 - min(max($hcm->value, 0), 1);
        $clientOn = $this->calculateOnBalance($clientOn, $balance, $limit, $hcm);
        if ($balance == 'off' or $limit) {
            $clientOff = $this->calculateOffBalance($clientOff, $clientOn, $balance, $limit, $hcm);
            $client    = $clientOff;
        } else {
            $client = $clientOn;
        }
        if ($limit and $limit == 'yes') {
            $clientLimitDirect->ead   = $clientLimitDirect->unused_direct_limit * $clientLimitDirect->ccf;
            $clientLimitDirect        = $this->calculateLimitDirectBalance($clientLimitDirect, $clientOn, $clientOff, $balance, $limit, $hcm);
            $clientLimitUnDirect->ead = $clientLimitUnDirect->unused_undirect_limit * $clientLimitDirect->ccf;
            $clientLimitUnDirect      = $this->calculateLimitUnDirectBalance($clientLimitUnDirect, $clientOn, $clientOff, $clientLimitDirect, $balance, $limit, $hcm);
            $client                   = $this->limitAccount($clientLimitDirect, $clientLimitUnDirect, 'on');
            $client                   = $this->limitAccount($client, $clientLimitDirect, 'off');
            $client->client_accounts  = [];
        }
        return $client;
    }

    public function fillNull($info)
    {
        $info->stage       = null;
        $info->stage_id    = null;
        $info->grade       = null;
        $info->final_grade = null;
        $info->pd          = null;
        $info->ecl         = null;
        $info->lgd         = null;
        return $info;
    }

    public function showQuarter($id, $quarter, $year, $balance = 'on', $limit = 'no')
    {
        $client              = Client::where('clients.id', $id)->joins()->with('attachments')->selectShow()->firstOrFail();
        $att                 = Attachment::where('attachmentable_id', $client->id)->get();
        $client->attachments = $att;
        return $this->calculateQuarter($client, $quarter, $year, $balance, $limit);
    }

    private function calculateQuarter($client, $quarter, $year, $balance = 'on', $limit = null)
    {
        foreach ($client->clientAccounts as $key0 => $account) {
            if (!$account->type_name) $ok = false;
            else $ok = in_array($account->type_name, DocumentType::$OFF_BALANCE_DOCUMENTS);
            if ((!$balance or $balance == 'on') and $ok) {
                unset($client->clientAccounts[$key0]);
                continue;
            } else if ($balance == 'off' and !$ok) {
                unset($client->clientAccounts[$key0]);
                continue;
            }


            foreach ($account->accountInfos as $key => $info) {
                if ($account->type_id == 1 or $account->type_id == 5) {
                    $loans += $info->outstanding_lcy;
                }
                if ($account->type_id == 4) {
                    $od += $info->outstanding_lcy;
                }

            }
            $client->od    = $od;
            $client->loans = $loans;

            $infos = $account->accountInfos()->where('year', $year)->where('quarter', $quarter)->get();
            foreach ($infos as $key => $info) {
                // To make the things go easier :)
                $info->client_id = $client->id;

                $info->class_type_id = $client->class_type_id;
                $info->irs_score     = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);
                $classType           = ClassType::find($info->class_type_id);
                if ($client->grade_id) {
                    $info->grade_id    = $client->grade_id;
                    $grade             = $client->grade->serial_no;
                    $info->final_grade = $client->grade->name;


                    // To handle central bank pd value
                    if ($classType->sub_category == 'central bank') {

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
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd != -1) {
                            $info->pd = $info->pd['final_calibrated_used_PD'][$grade];
                        } else {
                            $info->pd = null;
                        }
                    }

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }


                    $info = $this->finalLGD($info, $client->class_type_id, $limit);
                    $info = $this->eclCalc($info);

                } else if ($info->irs_score or $classType->sub_category == 'retail') {

                    if ($info->irs_score) {

                        $grade = (new ClientIRSProfileService())
                            ->getClientGradeId($client->financial_status, $info->irs_score);

                        $info->grade = Grade::where('class_type_id', $client->class_type_id)
                                            ->where('serial_no', $grade)->first()->name ?? 'WRONG SCORE VALUE';

                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays($grade, $info->past_due_days, $client->class_type_id);
                        $info->grade_id    = $grade;

                        $grade = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)->first();
                        $grade = $grade->serial_no;

                    } else if ($classType->sub_category == 'retail') {
                        $info->final_grade = (new ClientIRSProfileService())
                            ->gradePastDueDays(0, $info->past_due_days, $client->class_type_id);

                        if ($info->final_grade) {
                            $grade          = Grade::where('class_type_id', $client->class_type_id)->where('name', $info->final_grade)
                                                   ->first();
                            $info->grade_id = $grade->id;
                            $info->grade    = $grade->name;
                            $grade          = $grade->serial_no;
                        }


                    }


                    if ($info->stage_id == 3) {
                        $info->pd = 1;
                    }

                    if (!$info->pd) {
                        $info->pd = (new PDService())->getPdByYearQuarter($info->year, $info->quarter, $info->class_type_id);
                        if ($info->pd and $info->pd > -1) {
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

                    if ($client->stage_id) {
                        $stageModel     = Stage::where('id', $client->stage_id)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    } else {
                        $stage          = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $info->final_grade, $info);
                        $stageModel     = Stage::where('serial_no', $stage)->first();
                        $info->stage    = $stageModel->name ?? null;
                        $info->stage_no = $stageModel->serial_no ?? null;
                        $info->stage_id = $stageModel->id ?? null;
                    }
                } else {
                    $info = $this->fillNull($info);

                }

            }
            $eadSum = 0;
            foreach ($infos as $key => $info) {
                $eadSum += $info->ead;
            }
            foreach ($infos as $key => $info) {
                $info[$key]->ead_sum = $eadSum;
                $info[$key]          = $this->finalLGD($info, null, $limit, $balance);
                $info[$key]          = $this->eclCalc($info);
            }
            $account->accountInfos = $infos;
        }
//


        if ($limit and $limit == 'yes') {
            $client = $this->limitAccount($client);
        } else $client->limit_account = null;

        return $client;
    }

    public function showByCif($cif, $balance = 'on', $limit = null, $year = null, $quarter = null)
    {
        if (!$balance) $balance = 'on';
        $last = Client::allJoins()
                      ->where('clients.cif', $cif)
                      ->orderBy('year', 'desc')
                      ->orderBy('quarter', 'desc')
                      ->select('account_infos.year', 'account_infos.quarter')
                      ->distinct('year', 'quarter')
                      ->get();

        if (!$year or !$quarter) {
            if (count($last) > 0) {
                $year    = $last[0]->year;
                $quarter = $last[0]->quarter;
            } else {
                return abort(404);
            }
        }
        $client              = Client::where('clients.cif', $cif)->joins()->selectShow($year, $quarter)->firstOrFail();
        $att                 = Attachment::where('attachmentable_id', $client->id)->get();
        $client->attachments = $att;

        $client2              = Client::where('clients.cif', $cif)->joins()->selectShow($year, $quarter)->firstOrFail();
        $client2->attachments = $att;

        $client3              = Client::where('clients.cif', $cif)->joins()->selectShow($year, $quarter)->firstOrFail();
        $client3->attachments = $att;
        if ($limit == 'yes') {
            $client3 = $this->addUsedLimits($client3, $year, $quarter, $balance);
        }
        $client4              = Client::where('clients.cif', $cif)->joins()->selectShow($year, $quarter)->firstOrFail();
        $client4->attachments = $att;
        if ($limit == 'yes') {
            $client4 = $this->addUsedLimits($client4, $year, $quarter, $balance);
        }
        $client         = $this->calculate($client, $client2, $client3, $client4, $balance, $limit);
        $client->filter = $last;
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


    public function setGrade($id, $gradeId)
    {
        $client = Client::findOrFail($id);
        if ($gradeId == -1) {
            $client->grade_id = null;
        } else {
            $grade            = Grade::where('name', $gradeId)->where('class_type_id', $client->class_type_id)->firstOrFail();
            $client->grade_id = $grade->id;
        }
        $client->save();
        return true;
    }

    public function getClassTypeGrades($id)
    {
        $classType = ClassType::findOrFail($id);
        return $classType->grades()->select('id', 'name')->get();
    }

    public function addAttachments($id, $attachmentsIds)
    {
        $client = Client::findOrFail($id);
        if ($attachmentsIds) {
            foreach ($attachmentsIds as $id) {
                $attachment                      = Attachment::find($id);
                $attachment->attachmentable_id   = $client->id;
                $attachment->attachmentable_type = 'App\Models\Client';
                $attachment->save();
            }
        }
        return true;
    }

    public function setStage($id, $stageId)
    {
        $client = Client::findOrFail($id);
        if ($stageId == -1) {
            $client->stage_id = null;
        } else {
            $stage            = Grade::findOrFail($stageId);
            $client->stage_id = $stage->id;
        }

        $client->save();
        return true;
    }

    public function indexIRS(array $input)
    {
        $data    = $this->getClients($input);
        $service = new ClientIRSProfileService();
        foreach ($data['clients'] as $client) {
            $irsScore            = $service->calculateIrsScore(Carbon::now()->year, 'q' . Carbon::now()->quarter, $client->id);
            $temp                = $service->getGradeAndFinalGrade($client->financial_status, $client, $irsScore);
            $client->final_grade = "N/A";
            if (isset($temp['final_grade'])) {
                $client->final_grade = $temp['final_grade'];
            }
            if ($client->grade_id) {
                $client->final_score = 'Forced IRS';
            } else {
                if ($irsScore > 0) {
                    $client->final_score = round($irsScore, 2);
                } else if ($irsScore == 0 and !$client->grade_id) {
                    $client->final_score = 'N/A';
                    $client->final_grade = 'N/A';
                }
            }

        }
        return $data;
    }

    public function indexStage(array $input)
    {
        $data = $this->getClients($input, 'stage');

        $service    = new ClientStagingProfileService();
        $irsService = new ClientIRSProfileService();

        foreach ($data['clients'] as $client) {


            $irsScore = (new ClientIRSProfileService())
                ->calculateIrsScore(Carbon::now()->year, 'q' . Carbon::now()->quarter, $client->id);

            $temp = $irsService->getGradeAndFinalGrade($client->financial_status, $client, $irsScore);

            $client->stage_no = 'N/A';
            if ($temp['final_grade'] != 'N/A') {
                $accountIds = ClientAccount::where('client_id', $client->id)->get()->pluck('id')->toArray();
                $info       = AccountInfo::whereIn('client_account_id', $accountIds)->orderBy('id', 'desc')->first();
                try {
                    $client->stage_no = $service->calculateStaging('2018', 'q1', $client, $temp['final_grade'], $info);

                } catch (\Exception $e) {
                    dd("WTF");
                }
            }

            unset($client['client_accounts']);

            try {
                if ($client->stage_no != 'N/A') {
                    $client->stage_no = Stage::where('serial_no', $client->stage_no)->first()->name;
                }
            } catch (\Exception $e) {
                dd($client->stage_no);
            }

        }
        return $data;
    }

    private function getClients(array $input, $type = 'irs')
    {
        $withIds = [];
        if (!isset($input['class_type_category']) or $input['class_type_category'] == null) $input['class_type_category'] = 'facility';
        $data = Client::query();
        if ($type == 'irs') {
            $data->irsJoins($input['filter_type'] ?? 'all');
            if ($input['filter_type'] != 'all') {
                $withIds = Client::query();
                $withIds->whereNotNull('clients.grade_id');
                if (isset($input['class_type_id']) and $input['class_type_id'])
                    $withIds->where('class_type_id', $input['class_type_id']);
                $withIds = $withIds->get()->pluck('id')->toArray();
            }
        } else if ($type == 'stage') {
            $data->stageJoins($input['filter_type'] ?? 'all');
            if ($input['filter_type'] != 'all') {
                $withIds = Client::query();
                $withIds->whereNotNull('clients.stage_id');
                if (isset($input['class_type_id']) and $input['class_type_id'])
                    $withIds->where('class_type_id', $input['class_type_id']);
                $withIds = $withIds->get()->pluck('id')->toArray();
            }
        }

        if (isset($input['class_type_id']) and $input['class_type_id'])
            $data->where('class_type_id', $input['class_type_id']);
        if (isset($input['year']) and $input['year']) $data->where('account_infos.year', $input['year']);
        if (isset($input['quarter']) and $input['quarter']) $data->where('account_infos.quarter', $input['quarter']);

        // Class category filter (facility, financial)
        if (isset($input['class_type_category']))
            $data->where('class_types.category', $input['class_type_category']);

        $ids = $data->get()->pluck('client_id')->toArray();

        if ($input['filter_type'] == 'with') {
            $ids = array_merge($ids, $withIds);

        } else if ($input['filter_type'] == 'without') {
            $ids = array_diff($ids, $withIds);
        }
        $data = Client::query();
        $data = $data->whereIn('clients.id', $ids);
        $data->join('class_types', 'class_types.id', '=', 'clients.class_type_id');
        $data->select('clients.id', 'clients.cif', 'clients.name', 'clients.class_type_id',
                      'class_types.name as class_type_name', 'clients.financial_status',
                      'clients.grade_id', 'clients.stage_id');
        $data = $data->paginate($input['page_size']);

        return $this->handlePaginate($data, 'clients');
    }


}
