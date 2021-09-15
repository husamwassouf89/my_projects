<?php

namespace App\Traits;

use App\Models\Client\AccountInfo;

trait IFRS9
{
    public function finalLGD($account)
    {
        $account->final_lgd = $account->ead ? $account->lgd / $account->ead : 1;
        return $account;
    }

    public function LGD($account)
    {
        $account->lgd = $account->lgd_cm + $account->lgd_sec + $account->lgd_un;
        return $account;
    }

    public function LGDUn($account)
    {
//       X5*Classification-IFRS9
        $account->lgd_un = $account->uncovered;
        return $account;
    }

    public function LGDRe($account)
    {
//       X5*Classification-IFRS9
        $account->lgd_re = $account->covered_by_re;
        return $account;
    }

    public function LGDSec($account)
    {
//       X5*Classification-IFRS9
        $account->lgd_sec = $account->covered_by_sec;
        return $account;
    }

    public function LGDCm($account)
    {
//       X5*Classification-IFRS9
        $account->lgd_cm = $account->covered_by_cm;
        return $account;
    }

    public function uncovered($account)
    {
        $account->uncoverd = $account->ead
                             - $account->covered_by_cm
                             - $account->covered_by_secuirty
                             - $account->covered_by_re;
        return $account;
    }

    public function coveredByRe($account)
    {
        if ($account->account->main_currency_id == $account->account->guarntee_currency_id) {
            $account->covered_by_re = min($account->ead
                                          - $account->covered_by_cm
                                          - $account->covered_by_secuirty,
                                          $account->pv_guarantee_amount_by_re *
                                          $account->allocation_re_guarantee

            );
        } else {
            $account->covered_by_re = min($account->ead
                                          - $account->covered_by_cm
                                          - $account->covered_by_secuirty,
                                          $account->pv_guarantee_amount_by_re *
                                          $account->allocation_re_guarantee * 0.9);
        }
    }

    public function allocationOfReGuarantee($account)
    {
        if ($account->pv_re_guarantees != 0) {
            $temp = AccountInfo::join('client_accounts', 'client_accounts.client_id', '=', 'clients.id')
                               ->join('clients', 'clients.id', 'account_client.client_id')
                               ->where('cif', $account->account->client->cif)
                               ->get();

            $account = $this->ead($account);
            $sum     = 0;
            foreach ($temp as $item) {
                $item = $this->ead($item);
                $sum  += $item->ead;
            }

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

    public function ead($account): float
    {
        $outstanding        = $account->outstanding_lcy ?: 0;
        $accruedInterest    = $account->accrued_interest_lcy ?: 0;
        $accruedInterestLcy = $account->accrued_interest_lcy ?: 0;
        $suspendedLcy       = $account->suspended_lcy ?: 0;

        $account->ead = $outstanding + $accruedInterest - $accruedInterestLcy - $suspendedLcy;
        return $account;
    }

    public function pvGuaranteeAmountByRe($account)
    {
        $account->pv_guarantee_amount_re = null;
        return $account;
    }

    public function allocationOfCmGuarantee($account)
    {
        if ($account->pv_re_guarantees != 0) {
            $account->allocation_of_cm_guarantee = null;
        } else {
            $account->allocation_of_cm_guarantee = 0;
        }
        return $account;
    }


}
