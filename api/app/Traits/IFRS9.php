<?php

namespace App\Traits;

use App\Models\Client\AccountInfo;
use App\Models\Client\ClassType;
use App\Models\Client\ClientAccount;
use App\Models\Client\DocumentType;
use App\Models\Client\GuaranteeLGD;
use App\Models\Client\Predefined;
use App\Models\Value;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Object_;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\InterestRate;

trait IFRS9
{
    use HelpKit;

    public function finalLGD($account, $clientClassTypeId = null, $limit = null, $client = null)
    {
//        dd($clientClassTypeId);
        if ($clientClassTypeId) {
            $classType = ClassType::find($clientClassTypeId);
        }
        if ($clientClassTypeId and in_array($classType->sub_category, ['local bank', 'investments', 'abroad bank', 'central bank'])) {
            if (!$account->lgd) {
                $value = Predefined::where('grade_id', $account->grade_id)->where('class_type_id', $clientClassTypeId)->where('stage_id', $account->stage_id)->first();
//                dd($account->grade_id, $clientClassTypeId, $account->stage_id);
//                dd($value);
                if ($value and $value->lgd != -1) {
                    $account->lgd = $value->lgd;
                } else {
                    $account->lgd = -1;
                }
            }
            $account->final_lgd = $account->lgd;
        } else {
            $this->lgd($account);
            $account->final_lgd = $account->ead ? $account->lgd / $account->ead : 1;
//            dd($account->final_lgd,$account->lgd,$account->ead);

            $minValue = Value::find(3);
            if ($minValue and $minValue->value > 0) {
                if ($account->past_due_days) {
                    if ($account->past_due_days >= 360) {
                        $value              = Value::find(6);
                        $account->final_lgd = min($account->final_lgd, $value->value);
                    } else if ($account->past_due_days >= 180) {
                        $value              = Value::find(5);
                        $account->final_lgd = min($account->final_lgd, $value->value);
                    } else if ($account->past_due_days >= 90) {
                        $value              = Value::find(4);
                        $account->final_lgd = min($account->final_lgd, $value->value);
                    }
                }
            }
        }
        return $account;
    }

    public function ead(AccountInfo $account)
    {
        $outstanding      = $account->outstanding_lcy ?: 0;
        $accruedInterest  = $account->accrued_interest_lcy ?: 0;
        $interestReceived = $account->interest_received_in_advance_lcy ?: 0;
        $suspendedLcy     = $account->suspended_lcy ?: 0;
        $account->ead     = $outstanding + $accruedInterest - $interestReceived - $suspendedLcy;
        if ($account->account->documentType) {
            $ccf = $account->account->documentType->ccf ?? 1;
        } else {
            $ccf = 1;
        }
        $account->ccf = $ccf;
        $account->ead = $account->ead * $ccf;
        return $account;
    }

    private function calculateRemainingGuarantee($client, $clientOn, $clientOff)
    {
        $cm      = 0;
        $re      = 0;
        $sec     = 0;
        $ok      = false;
        $account = null;
        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                if (!$ok) {
                    $account = AccountInfo::find($info->id);
                }
                $cm  += max($info->cm_guarantee - $info->covered_by_cm, 0);
                $re  += max($info->pv_re_guarantees - $info->covered_by_re, 0);
                $sec += max($info->pv_securities_guarantees - $info->covered_by_sec, 0);
            }
        }
        $account->cm_guarantee             = $cm;
        $account->pv_re_guarantees         = $re;
        $account->pv_securities_guarantees = $sec;
        return $account;
    }


    public function eclLimitCalculations($client, $account)
    {

        $account->days = 365;
        $repayments    = [];
        $date          = Carbon::createFromDate($account->mat_date);
        $startDate     = Carbon::createFromDate($account->st_date);
        $valuationDate = Carbon::createFromDate($this->getDateRange($account->year, $account->quarter)['last_date']);
        $temp          = Carbon::createFromDate($this->getDateRange($account->year, $account->quarter)['last_date']);
        $temp->addDays(365);
        if ($temp < $date) {
            $date12 = $temp;
        } else {
            $date12 = $date;
        }
        $freq         = 1;
        $ead          = $account['ead'];
        $pd           = $client->pd;
        $lgd          = $account['final_lgd'];
        $discountRate = 0;
        $lecl         = 0;
        $eclM12       = 0;
        $day          = $date->format('d');
        $month        = $date->format('m');
        $year         = $date->format('Y');
        array_push($repayments, $date->toDateString());


        $count = 0;
        while (true) {
            $count += 1;
            $month = ceil($month);
            if ($month - $freq <= 0) {
                $nextMonth = ($month - $freq) + 12;
            } else {
                $nextMonth = $month - $freq;
            }
            if ($nextMonth >= $month) $year = $year - 1;
            $month       = $nextMonth;
            $currentDate = Carbon::createFromDate($year, $month, $day);

            if ($currentDate <= $startDate) {
                $currentDate = $startDate;
            }
            array_push($repayments, $currentDate->toDateString());
            if ($currentDate <= $valuationDate or $valuationDate < $startDate) {
                break;
            }

        }
        $data      = [];
        $lastValue = null;

        if ($account->number_of_installments == 0) $repaymentDivisor = 1;
        else {
            $repaymentDivisor = count($repayments) - 1;
        }

        $repaymentAmount = $ead / $repaymentDivisor;
        $two             = false;


        foreach ($repayments as $key => $repayment) {
            $value                   = [];
            $value['repayment_date'] = $repayment;
            $value['days_between']   = 0;

            if ($key == 0) {
                $value['repayment_indicator'] = 1;
            } else {
                if ($account->number_of_installments == 0) {
                    $value['repayment_indicator'] = 0;
                } else {
                    if ($key == count($repayments) - 1) {
                        $value['repayment_indicator'] = 0;
                    } else {
                        $value['repayment_indicator'] = 1;
                    }
                }
            }

            $value['ead_end_of_period'] = 0;
            $value['repayment']         = $repaymentAmount;
            $value['days_for_discount'] = 0;
            $value['pd_cum']            = 0;
            $value['lgd']               = $lgd;
            $value['discount_rate']     = 0;
            if (count($data) != 0) {
                $value['days_between'] = $this->daysBetweenTwoDates($repayment, $repayments[$key - 1]);

                if (!$two) {
                    if ($date > $valuationDate) {
                        $value['days_for_discount'] = $this->daysBetweenTwoDates($date->toDateString(), $valuationDate->toDateString());
                    } else {
                        $value['days_for_discount'] = 360;
                    }
                    $two = true;
                } else {
                    if ($lastValue['days_for_discount'] == 0) {
                        $value['days_for_discount'] = 0;
                    } else {
                        $value['days_for_discount'] = $lastValue['days_for_discount'] - $lastValue['days_between'];
                    }
                }


                $value['repayment']         = $repaymentAmount * $value['repayment_indicator'];
                $value['ead_end_of_period'] = $lastValue['ead_end_of_period'] + $lastValue['repayment'];
                $value['pd_cum']            = 1 - pow(1 - $pd, $value['days_for_discount'] / 365);
                $value['discount_rate']     = pow(1 / (1 + $discountRate), $value['days_for_discount'] / 360);

            }


            $lastValue = $value;
            array_push($data, $value);
        }

        $data = array_reverse($data);
        foreach ($data as $key => $item) {
            if ($key > 0 and $key < count($data) - 1) {
                $data[$key]['pd_marginal'] = abs($data[$key - 1]['pd_cum'] - $item['pd_cum']);
            } else if ($key == 0) {
                $data[$key]['pd_marginal'] = $item['pd_cum'];
            } else {
                $data[$key]['pd_marginal'] = 0;
            }
            $data[$key]['el'] = $data[$key]['ead_end_of_period'] * $data[$key]['discount_rate'] * $data[$key]['lgd'] * $data[$key]['pd_marginal'];

            if ($key == count($data) - 1) {
                $data[$key]['cum_el'] = 0;
            } else if ($key) {
                $data[$key]['cum_el'] = $data[$key - 1]['cum_el'] + $data[$key]['el'];
            } else {
                $data[$key]['cum_el'] = $data[$key]['el'];
            }

            if ($date12 > Carbon::createFromDate($item['repayment_date'])) {
                $temp                    = $this->daysBetweenTwoDates($date12->toDateString(), $item['repayment_date']);
                $data[$key]['temp_days'] = $temp;
                if ($item['days_between'])
                    $selector = min($temp / $item['days_between'], 1);
                else $selector = 0;
            } else {
                $selector = 0;
            }

            $data[$key]['12_m_selector'] = $selector;
            $data[$key]['12_m_el']       = $data[$key]['el'] * $data[$key]['12_m_selector'];

            $lecl   += $data[$key]['el'];
            $eclM12 += $data[$key]['12_m_el'];
        }

//        $data = array_reverse($data);

        $account->ecl_data = $data;
        if ($account->stage_no == 2) {
            $account->ecl = $lecl;
        } else {
            $account->ecl = $eclM12;
        }


        return $account;

    }


    public function limitAccount($client, $client2, $balance)
    {
        $account = $client2->clientAccounts[0]->accountInfos[0];
        unset($account->id);
        unset($account->client_account_id);
        unset($account->outstanding_fcy);
        unset($account->outstanding_lcy);
        unset($account->accrued_interest_lcy);
        unset($account->suspended_lcy);
        unset($account->interest_received_in_advance_lcy);
        unset($account->st_date);
        unset($account->mat_date);
        unset($account->sp_date);
        unset($account->past_due_days);
        unset($account->number_of_reschedule);
        unset($account->estimated_value_of_stock_collateral);
        unset($account->mortgages);
        unset($account->estimated_value_of_real_estate_collateral);
        unset($account['80_per_estimated_value_of_real_estate_collateral']);
        unset($account->interest_rate);
        unset($account->pay_method);
        unset($account->number_of_installments);
        unset($account->created_at);
        unset($account->client_id);
        unset($account->class_type_id);
        unset($account->irs_score);
        unset($account->grade_id);
        unset($account->final_grade);
        unset($account->stage);
        unset($account->stage_no);
        unset($account->stage_id);
        unset($account->ead_sum);
        unset($account->allocation_cm_guarantee);
        unset($account->allocation_sec_guarantee);
        unset($account->allocation_re_guarantee);
        if($balance == 'on')
        {$client->direct_limit_account = $account;}
        else {
            $client->undirect_limit_account = $account;
        }
        $client->client_accounts = [];
        return $client;
    }

    public function lgdLimit($account)
    {
        $account            = $this->lgdUnLimit($account);
        $account            = $this->lgdReLimit($account);
        $account            = $this->lgdSecLimit($account);
        $account            = $this->lgdCmLimit($account);
        $account->lgd       = $account->lgd_cm + $account->lgd_sec + $account->lgd_un + $account->lgd_re;
        $account->final_lgd = $account->ead ? $account->lgd / $account->ead : 1;
        return $account;
    }

    public function lgd($account)
    {
//        LGD BUILDER (TO REDUCE COUPLING) ORDER IS IMPORTANT
        $account = $this->allocationOfCmGuarantee($account);
        $account = $this->coveredByCm($account);
        $account = $this->lgdCm($account);

        $account = $this->allocationOfSecGuarantee($account);
        $account = $this->coveredBySec($account);
        $account = $this->lgdSec($account);

        $account = $this->allocationOfReGuarantee($account);
        $account = $this->coveredByRe($account);
        $account = $this->lgdRe($account);

        $account = $this->uncovered($account);
        $account      = $this->lgdUn($account);
        $account->lgd = $account->lgd_cm + $account->lgd_sec + $account->lgd_un + $account->lgd_re;
        return $account;
    }

    public function lgdUn($account)
    {
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 4);
        $account->lgd_un = $account->uncovered * $c;
        $account->ccf_lgd_un = $c ;
        return $account;
    }

    public function lgdUnLimit($account)
    {
        $account         = $this->uncoveredLimit($account);
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 4);
        $account->lgd_un = $account->uncovered * $c;
        return $account;
    }

    public function uncovered($account)
    {
        $account->uncovered = $account->ead
                              - $account->covered_by_cm
                              - $account->covered_by_sec
                              - $account->covered_by_re;
        return $account;
    }

    public function uncoveredLimit($account)
    {
        $account            = $this->coveredByReLimit($account);
        $account->uncovered = $account->ead
                              - $account->covered_by_cm
                              - $account->covered_by_sec
                              - $account->covered_by_re;
        return $account;
    }

    public function coveredByReLimit($account)
    {
        $account = $this->coveredBySecLimit($account);
        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarantee_currency_id) {
            $account->covered_by_re = min($account->ead
                                          - $account->covered_by_cm
                                          - $account->covered_by_sec,
                                          $account->pv_re_guarantees

            );
        } else {
            $account->covered_by_re = min($account->ead
                                          - $account->covered_by_cm
                                          - $account->covered_by_sec,
                                          $account->pv_re_guarantees * $account->hcm);
        }

        return $account;
    }

    public function coveredByRe($account)
    {
        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarantee_currency_id) {
            $account->covered_by_re = min($account->ead
                                          - $account->covered_by_cm
                                          - $account->covered_by_sec,
                                          $account->pv_re_guarantees
                                          *
                                          $account->allocation_re_guarantee

            );
        } else {
            $account->covered_by_re = min($account->ead
                                          - $account->covered_by_cm
                                          - $account->covered_by_sec,
                                          $account->pv_re_guarantees *
                                          $account->allocation_re_guarantee * $account->hcm);
        }

        return $account;
    }

    public function coveredBySecLimit($account)
    {
        $account = $this->coveredByCmLimit($account);
        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarantee_currency_id) {
            $account->covered_by_sec = min($account->ead
                                           - $account->covered_by_cm,
                                           $account->pv_securities_guarantees

            );
        } else {
            $account->covered_by_sec = min($account->ead
                                           - $account->covered_by_cm,
                                           $account->pv_securities_guarantees * $account->hcm);
        }
        return $account;

    }

    public function coveredBySec($account)
    {
        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarantee_currency_id) {
            $account->covered_by_sec = min($account->ead
                                           - $account->covered_by_cm,
                                           $account->pv_securities_guarantees
                                           *
                                           $account->allocation_sec_guarantee

            );
        } else {
            $account->covered_by_sec = min($account->ead
                                           - $account->covered_by_cm,
                                           $account->pv_securities_guarantees *
                                           $account->allocation_sec_guarantee * $account->hcm);
        }
        return $account;

    }

    public function coveredByCmLimit($account)
    {
        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarantee_currency_id) {
            $account->covered_by_cm = min($account->ead,
                                          $account->cm_guarantee);
        } else {
            $account->covered_by_cm = min($account->ead,
                                          $account->cm_guarantee * $account->hcm);
        }
        return $account;
    }

    public function coveredByCm($account)
    {
        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarantee_currency_id) {
            $account->covered_by_cm = min($account->ead,
                                          $account->cm_guarantee
                                          *
                                          $account->allocation_cm_guarantee

            );
        } else {
            $account->covered_by_cm = min($account->ead,
                                          $account->cm_guarantee *
                                          $account->allocation_cm_guarantee * $account->hcm);
        }

        return $account;
    }

    public function allocationOfCmGuarantee(AccountInfo $account): AccountInfo
    {
        if (isset($account->cm_guarantee) and $account->cm_guarantee != 0 and $account->ead > 0) {
            $account->allocation_cm_guarantee = 1;
        } else {
            $account->allocation_cm_guarantee = 0;
        }
        return $account;
    }

    private function getEadSum($account)
    {
        $value = Value::find(1);

        $temp = AccountInfo::query();
        $temp->join('client_accounts', 'client_accounts.id', '=', 'account_infos.client_account_id');
        $temp->join('clients', 'clients.id', 'client_accounts.client_id');
        $temp->where('cif', $this->clientAccount($account)->client->cif);
        if ($value->value) {
            $temp->where('client_accounts.type_id', '=', 2);
        }
        $temp = $temp->get();
        $sum = 0;
        foreach ($temp as $item) {
            $item = $this->ead($item);
            $sum  += $item->ead;
        }
        return $sum;
    }

    private function clientAccount($info)
    {
        return ClientAccount::where('id', $info->client_account_id)->first();
    }

    public function allocationOfSecGuarantee(AccountInfo $account): AccountInfo
    {
        $sum = $account->ead_sum;
        if (($account->pv_securities_guarantees) and $account->pv_securities_guarantees != 0 and $sum > 0) {
            $account->allocation_cm_guarantee = min(1, $account->ead / $sum);
        } else {
            $account->allocation_sec_guarantee = 0;
        }
        return $account;
    }

    public function allocationOfReGuarantee(AccountInfo $account): AccountInfo
    {
        $sum = $account->ead_sum;
        if (isset($account->pv_re_guarantees) and $account->pv_re_guarantees != 0 and $sum > 0) {
            $account->allocation_re_guarantee = min(1, $account->ead / $sum);
        } else {
            $account->allocation_re_guarantee = 0;
        }
        return $account;
    }

    private function getLGDRatio($stageId, $classTypeId, $guaranteeId): float
    {

        $c = GuaranteeLGD::where('stage_id', $stageId)->where('class_type_id', $classTypeId)->where('guarantee_id', $guaranteeId)->first();
        if ($c) {
            return $c->ratio;
        } else {
            return 0;
        }
    }

    public function lgdRe($account)
    {
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 3);
        $account->lgd_re = $account->covered_by_re * $c;
        $account->ccf_lgd_re = $c ;
        return $account;
    }

    public function lgdReLimit($account)
    {
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 3);
        $account->lgd_re = $account->covered_by_re * $c;
        return $account;
    }

    public function lgdSec($account)
    {
        $c                = $this->getLGDRatio($account->stage_id, $account->class_type_id, 2);
        $account->lgd_sec = $account->covered_by_sec * $c;
        $account->ccf_lgd_sec = $c ;
        return $account;
    }

    public function lgdSecLimit($account)
    {
        $c                = $this->getLGDRatio($account->stage_id, $account->class_type_id, 2);
        $account->lgd_sec = $account->covered_by_sec * $c;
        return $account;
    }

    public function lgdCm($account)
    {
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 1);
        $account->lgd_cm = $account->covered_by_cm * $c;
        $account->ccf_lgd_cm = $c ;
        return $account;
    }

    public function lgdCmLimit($account)
    {
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 1);
        $account->lgd_cm = $account->covered_by_cm * $c;
        return $account;
    }

    public function eclCalc($account, $limit = 'no')
    {
        if ($account->stage_no == 3) {
            $account->ecl = $account->pd * $account->final_lgd * $account->ead;
            return $account;
        }
        if($limit == 'yes'){
            return $this->eclCalculationsLimit($account, true);
        } else{
            return $this->eclCalculations($account, true);
        }
    }

    public function eclCalculationsLimit($account, $isData = false)
    {
        $account->number_of_installments = 0;
        $account->freq                   = 12;
        $account->days                   = 365 / $account->freq;

        $repayments    = [];
        $valuationDate = Carbon::createFromDate($this->getDateRange($account->year, $account->quarter)['last_date']);
        $temp          = Carbon::createFromDate($this->getDateRange($account->year, $account->quarter)['last_date']);
        $temp->addDays(365);
        $date12       = $temp;
        $date          = $temp;
        $freq         = $account->freq;
        $ead          = $account->ead;
        $pd           = $account->pd;
        $lgd          = $account->final_lgd;
        $discountRate = 0;
        $lecl         = 0;
        $eclM12       = 0;
        $day          = $date->format('d');
        $month        = $date->format('m');
        $year         = $date->format('Y');
        array_push($repayments, $date->toDateString());


        $count = 0;
        while (true) {
            $count += 1;
            $month = ceil($month);
            if ($month - $freq <= 0) {
                $nextMonth = ($month - $freq) + 12;
            } else {
                $nextMonth = $month - $freq;
            }
            if ($nextMonth >= $month) $year = $year - 1;
            $month       = $nextMonth;
            $currentDate = Carbon::createFromDate($year, $month, $day);

            if ($currentDate <= $valuationDate) {
                break;
            }
            array_push($repayments, $currentDate->toDateString());

        }

        $data      = [];
        $lastValue = null;


        $repaymentAmount = $ead;
        $two             = false;

        foreach ($repayments as $key => $repayment) {
            $value                   = [];
            $value['repayment_date'] = $repayment;
            $value['days_between']   = 0;

            if ($key == 0) {
                $value['repayment_indicator'] = 1;
            } else {
                if ($account->number_of_installments == 0) {
                    $value['repayment_indicator'] = 0;
                } else {
                    if ($key == count($repayments) - 1) {
                        $value['repayment_indicator'] = 0;
                    } else {
                        $value['repayment_indicator'] = 1;
                    }
                }
            }

            $value['ead_end_of_period'] = 0;
            $value['repayment']         = $repaymentAmount;
            $value['days_for_discount'] = 0;
            $value['pd_cum']            = 0;
            $value['lgd']               = $lgd;
            $value['discount_rate']     = 0;
            if (count($data) != 0) {
                $value['days_between'] = $this->daysBetweenTwoDates($repayment, $repayments[$key - 1]);

                if (!$two) {
                    if ($date > $valuationDate) {
                        $value['days_for_discount'] = $this->daysBetweenTwoDates($date->toDateString(), $valuationDate->toDateString());
                    } else {
                        $value['days_for_discount'] = 360;
                    }
                    $two = true;
                } else {
                    if ($lastValue['days_for_discount'] == 0) {
                        $value['days_for_discount'] = 0;
                    } else {
                        $value['days_for_discount'] = $lastValue['days_for_discount'] - $lastValue['days_between'];
                    }
                }


                $value['repayment']         = $repaymentAmount * $value['repayment_indicator'];
                $value['ead_end_of_period'] = $lastValue['ead_end_of_period'] + $lastValue['repayment'];
                $value['pd_cum']            = 1 - pow(1 - $pd, $value['days_for_discount'] / 365);
                $value['discount_rate']     = pow(1 / (1 + $discountRate), $value['days_for_discount'] / 360);

            }


            $lastValue = $value;
            array_push($data, $value);
        }

        $data = array_reverse($data);
        foreach ($data as $key => $item) {
            if ($key > 0 and $key < count($data) - 1) {
                $data[$key]['pd_marginal'] = abs($data[$key - 1]['pd_cum'] - $item['pd_cum']);
            } else if ($key == 0) {
                $data[$key]['pd_marginal'] = $item['pd_cum'];
            } else {
                $data[$key]['pd_marginal'] = 0;
            }
            $data[$key]['el'] = $data[$key]['ead_end_of_period'] * $data[$key]['discount_rate'] * $data[$key]['lgd'] * $data[$key]['pd_marginal'];

            if ($key == count($data) - 1) {
                $data[$key]['cum_el'] = 0;
            } else if ($key) {
                $data[$key]['cum_el'] = $data[$key - 1]['cum_el'] + $data[$key]['el'];
            } else {
                $data[$key]['cum_el'] = $data[$key]['el'];
            }

            if ($date12 > Carbon::createFromDate($item['repayment_date'])) {
                $temp                    = $this->daysBetweenTwoDates($date12->toDateString(), $item['repayment_date']);
                $data[$key]['temp_days'] = $temp;
                if ($item['days_between'])
                    $selector = min($temp / $item['days_between'], 1);
                else $selector = 0;
            } else {
                $selector = 0;
            }

            $data[$key]['12_m_selector'] = $selector;
            $data[$key]['12_m_el']       = $data[$key]['el'] * $data[$key]['12_m_selector'];

            $lecl   += $data[$key]['el'];
            $eclM12 += $data[$key]['12_m_el'];
        }

//        $data = array_reverse($data);

        $account->ecl_data = $data;
        if ($account->stage_no == 2) {
            $account->ecl = $lecl;
        } else {
            $account->ecl = $eclM12;
        }


        return $account;

    }


    public function eclCalculations($account, $isData = false)
    {
        if ($account->number_of_installments != 0) {
            $account->freq = 12 / $account->number_of_installments;
        } else {
            $account->freq = 12;
        }

        $account->days = 365 / $account->freq;

        $repayments    = [];
        $date          = Carbon::createFromDate($account->mat_date);
        $startDate     = Carbon::createFromDate($account->st_date);
        $valuationDate = Carbon::createFromDate($this->getDateRange($account->year, $account->quarter)['last_date']);
        $temp          = Carbon::createFromDate($this->getDateRange($account->year, $account->quarter)['last_date']);
        $temp->addDays(365);
        if ($temp < $date) {
            $date12 = $temp;
        } else {
            $date12 = $date;
        }
        $freq         = ceil($account->freq);
        $ead          = $account->ead;
        $pd           = $account->pd;
        $lgd          = $account->final_lgd;
        $discountRate = $account->interest_rate;
        if ($account->number_of_installments and $account->number_of_installments > 0) {
            $discountRate = InterestRate::effective($discountRate, $account->number_of_installments);
        }
        $lecl   = 0;
        $eclM12 = 0;
        $day    = $date->format('d');
        $month  = $date->format('m');
        $year   = $date->format('Y');
        array_push($repayments, $date->toDateString());


        $count = 0;
        while (true) {
            $count += 1;
            $month = ceil($month);
            if ($month - $freq <= 0) {
                $nextMonth = ($month - $freq) + 12;
            } else {
                $nextMonth = $month - $freq;
            }
            if ($nextMonth >= $month) $year = $year - 1;
            $month       = $nextMonth;
            $currentDate = Carbon::createFromDate($year, $month, $day);

            if ($currentDate <= $startDate) {
                $currentDate = $startDate;
            }
            array_push($repayments, $currentDate->toDateString());
            if ($currentDate <= $valuationDate or $valuationDate < $startDate) {
                break;
            }

        }
        $data      = [];
        $lastValue = null;

        if ($account->number_of_installments == 0) $repaymentDivisor = 1;
        else {
            $repaymentDivisor = count($repayments) - 1;
        }

        $repaymentAmount = $ead / $repaymentDivisor;
        $two             = false;


        foreach ($repayments as $key => $repayment) {
            $value                   = [];
            $value['repayment_date'] = $repayment;
            $value['days_between']   = 0;

            if ($key == 0) {
                $value['repayment_indicator'] = 1;
            } else {
                if ($account->number_of_installments == 0) {
                    $value['repayment_indicator'] = 0;
                } else {
                    if ($key == count($repayments) - 1) {
                        $value['repayment_indicator'] = 0;
                    } else {
                        $value['repayment_indicator'] = 1;
                    }
                }
            }

            $value['ead_end_of_period'] = 0;
            $value['repayment']         = $repaymentAmount;
            $value['days_for_discount'] = 0;
            $value['pd_cum']            = 0;
            $value['lgd']               = $lgd;
            $value['discount_rate']     = 0;
            if (count($data) != 0) {
                $value['days_between'] = $this->daysBetweenTwoDates($repayment, $repayments[$key - 1]);

                if (!$two) {
                    if ($date > $valuationDate) {
                        $value['days_for_discount'] = $this->daysBetweenTwoDates($date->toDateString(), $valuationDate->toDateString());
                    } else {
                        $value['days_for_discount'] = 360;
                    }
                    $two = true;
                } else {
                    if ($lastValue['days_for_discount'] == 0) {
                        $value['days_for_discount'] = 0;
                    } else {
                        $value['days_for_discount'] = $lastValue['days_for_discount'] - $lastValue['days_between'];
                    }
                }


                $value['repayment']         = $repaymentAmount * $value['repayment_indicator'];
                $value['ead_end_of_period'] = $lastValue['ead_end_of_period'] + $lastValue['repayment'];
                $value['pd_cum']            = 1 - pow(1 - $pd, $value['days_for_discount'] / 365);
                $value['discount_rate']     = pow(1 / (1 + $discountRate), $value['days_for_discount'] / 360);

            }


            $lastValue = $value;
            array_push($data, $value);
        }

        $data = array_reverse($data);
        foreach ($data as $key => $item) {
            if ($key > 0 and $key < count($data) - 1) {
                $data[$key]['pd_marginal'] = abs($data[$key - 1]['pd_cum'] - $item['pd_cum']);
            } else if ($key == 0) {
                $data[$key]['pd_marginal'] = $item['pd_cum'];
            } else {
                $data[$key]['pd_marginal'] = 0;
            }
            $data[$key]['el'] = $data[$key]['ead_end_of_period'] * $data[$key]['discount_rate'] * $data[$key]['lgd'] * $data[$key]['pd_marginal'];

            if ($key == count($data) - 1) {
                $data[$key]['cum_el'] = 0;
            } else if ($key) {
                $data[$key]['cum_el'] = $data[$key - 1]['cum_el'] + $data[$key]['el'];
            } else {
                $data[$key]['cum_el'] = $data[$key]['el'];
            }

            if ($date12 > Carbon::createFromDate($item['repayment_date'])) {
                $temp                    = $this->daysBetweenTwoDates($date12->toDateString(), $item['repayment_date']);
                $data[$key]['temp_days'] = $temp;
                if ($item['days_between'])
                    $selector = min($temp / $item['days_between'], 1);
                else $selector = 0;
            } else {
                $selector = 0;
            }

            $data[$key]['12_m_selector'] = $selector;
            $data[$key]['12_m_el']       = $data[$key]['el'] * $data[$key]['12_m_selector'];

            $lecl   += $data[$key]['el'];
            $eclM12 += $data[$key]['12_m_el'];
        }

//        $data = array_reverse($data);

        $account->ecl_data = $data;
        if ($account->stage_no == 2) {
            $account->ecl = $lecl;
        } else {
            $account->ecl = $eclM12;
        }


        return $account;

    }

    private function daysBetweenTwoDates($date1, $date2)
    {
        $date1 = Carbon::createFromDate($date1);
        $date2 = Carbon::createFromDate($date2);
        return $date1->floatDiffInRealDays($date2);
    }


}
