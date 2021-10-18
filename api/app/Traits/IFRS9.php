<?php

namespace App\Traits;

use App\Models\Client\AccountInfo;
use App\Models\Client\ClientAccount;
use App\Models\Client\GuaranteeLGD;
use Carbon\Carbon;

trait IFRS9
{
    public function finalLGD($account)
    {
        $this->ead($account);
        $this->lgd($account);
        $account->final_lgd = $account->ead ? $account->lgd / $account->ead : 1;
        return $account;
    }

    public function ead(AccountInfo $account)
    {
        $outstanding      = $account->outstanding_lcy ?: 0;
        $accruedInterest  = $account->accrued_interest_lcy ?: 0;
        $interestReceived = $account->interest_received_in_advance_lcy ?: 0;
        $suspendedLcy     = $account->suspended_lcy ?: 0;

        $account->ead = $outstanding + $accruedInterest - $interestReceived - $suspendedLcy;
        return $account;
    }

    public function lgd($account)
    {
        $account      = $this->lgdUn($account);
        $account      = $this->lgdRe($account);
        $account      = $this->lgdSec($account);
        $account      = $this->lgdCm($account);
        $account->lgd = $account->lgd_cm + $account->lgd_sec + $account->lgd_un + $account->lgd_re;
        return $account;
    }

    public function lgdUn($account)
    {
        $account         = $this->uncovered($account);
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 4);
        $account->lgd_un = $account->uncovered * $c;
        return $account;
    }

    public function uncovered($account)
    {
        $account            = $this->coveredByRe($account);
        $account->uncovered = $account->ead
                              - $account->covered_by_cm
                              - $account->covered_by_sec
                              - $account->covered_by_re;
        return $account;
    }

    public function coveredByRe($account)
    {
        $account = $this->coveredBySec($account);
        $account = $this->allocationOfReGuarantee($account);


        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarntee_currency_id) {
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
                                          $account->allocation_re_guarantee * 0.9);
        }

        return $account;
    }

    public function coveredBySec($account)
    {
        $account = $this->coveredByCm($account);
        $account = $this->allocationOfSecGuarantee($account);

        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarntee_currency_id) {
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
                                           $account->allocation_sec_guarantee * 0.9);
        }
        return $account;

    }

    public function coveredByCm($account)
    {
        $account = $this->allocationOfCmGuarantee($account);

        if ($this->clientAccount($account)->main_currency_id == $this->clientAccount($account)->guarntee_currency_id) {
            $account->covered_by_cm = min($account->ead,
                                          $account->cm_guarantee
                                          *
                                          $account->allocation_cm_guarantee

            );
        } else {
            $account->covered_by_cm = min($account->ead,
                                          $account->cm_guarantee *
                                          $account->allocation_cm_guarantee * 0.9);
        }

        return $account;
    }

    public function allocationOfCmGuarantee(AccountInfo $account): AccountInfo
    {
        if ($account->cm_guarantee != 0) {
            $sum = $this->getEadSum($account);

            if (isset($account->cm_guarantee) and $account->cm_guarantee > $sum) {
                $account->allocation_cm_guarantee = 1;
            } else {
                $account->allocation_cm_guarantee = $account->ead / $sum;
            }
        } else {
            $account->allocation_cm_guarantee = 0;
        }
        return $account;
    }

    private function getEadSum($account)
    {
        $temp = AccountInfo::join('client_accounts', 'client_accounts.id', '=', 'account_infos.client_account_id')
                           ->join('clients', 'clients.id', 'client_accounts.client_id')
                           ->where('cif', $this->clientAccount($account)->client->cif)
                           ->get();

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
        if ($account->pv_securities_guarantees != 0) {
            $sum = $this->getEadSum($account);

            if (isset($account->pv_securities_guarantees) and $account->pv_securities_guarantees > $sum) {
                $account->allocation_sec_guarantee = 1;
            } else {
                $account->allocation_sec_guarantee = $account->ead / $sum;
            }
        } else {
            $account->allocation_sec_guarantee = 0;
        }
        return $account;
    }

    public function allocationOfReGuarantee(AccountInfo $account): AccountInfo
    {
        if ($account->pv_re_guarantees != 0) {
            $sum = $this->getEadSum($account);

            if (isset($account->pv_re_guarantees) and $account->pv_re_guarantees > $sum) {
                $account->allocation_re_guarantee = 1;
            } else {
                $account->allocation_re_guarantee = $account->ead / $sum;
            }
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
        $account         = $this->coveredByRe($account);
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 3);
        $account->lgd_re = $account->covered_by_re * $c;
        return $account;
    }

    public function lgdSec($account)
    {
        $c                = $this->getLGDRatio($account->stage_id, $account->class_type_id, 2);
        $account->lgd_sec = $account->covered_by_sec * $c;
        return $account;
    }

    public function lgdCm($account)
    {
        $c               = $this->getLGDRatio($account->stage_id, $account->class_type_id, 1);
        $account->lgd_cm = $account->covered_by_cm * $c;
        return $account;
    }

    public function eclCalc($account)
    {
        if ($account->stage_no == 3) {
            $account->ecl = $account->pd * $account->final_lgd * $account->ead;
            return $account;
        }
        return $this->eclCalculations($account, true);
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
        $valuationDate = Carbon::createFromDate('2021-3-31');
        $temp          = Carbon::createFromDate('2021-3-31');
        $temp->addDays(365);
        if ($temp < $date) {
            $date12 = $temp;
        } else {
            $date12 = $date;
        }
        $freq         = $account->freq;
        $ead          = $account->ead;
        $pd           = $account->pd;
        $lgd          = $account->final_lgd;
        $discountRate = $account->interest_rate;
        $lecl         = 0;
        $eclM12       = 0;
        $day          = $date->format('d');
        $month        = $date->format('m');
        $year         = $date->format('Y');
        array_push($repayments, $date->toDateString());
        while (true) {
            if ($month <= 1) {
                $nextMonth = ($month - $freq) + 12;
            } else {
                $nextMonth = $month - $freq;
            }
            if ($nextMonth >= $month) $year = $year - 1;
            $month       = $nextMonth;
            $currentDate = Carbon::createFromDate($year, $month, $day);
            if ($currentDate <= $valuationDate) {
                $currentDate = $valuationDate;
            }
            array_push($repayments, $currentDate->toDateString());
            if ($currentDate <= $valuationDate) break;
        }
        $data            = [];
        $lastValue       = null;
        $repaymentAmount = $ead / (count($repayments) - 1);
        $two             = false;

        foreach ($repayments as $key => $repayment) {
            $value                   = [];
            $value['repayment_date'] = $repayment;
            $value['days_between']   = 0;

            if ($key != count($repayments) - 1) {
                $value['repayment_indicator'] = 1;
            } else {
                $value['repayment_indicator'] = 0;
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
