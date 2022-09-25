<?php

namespace App\Imports;

use App\Models\Client\AccountInfo;
use App\Models\Client\Branch;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\ClientAccount;
use App\Models\Client\Currency;
use App\Models\Client\Grade;
use App\Models\Client\Type;
use App\Traits\HelpKit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ClientImport implements ToCollection
{

    use HelpKit;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $ok = true;
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;

            if (!$row[1] or !$row[3]) continue;

            if (request()->replace == true and $ok == true) {
                $id  = ClassType::firstOrCreate(['name' => $row[3]])->id;
                $ids = AccountInfo::join('client_accounts', 'account_infos.client_account_id', '=', 'client_accounts.id')
                                  ->join('clients', 'client_accounts.client_id', '=', 'clients.id')
                                  ->join('class_types', 'class_types.id', '=', 'clients.class_type_id')
                                  ->where('class_types.category', '=', 'facility')
                                  ->whereNotIn('client_accounts.type_id', [9, 10, 11])
                                  ->where('account_infos.year', '=', request()->year)
                                  ->where('account_infos.quarter', '=', request()->quarter)
                                  ->select('account_infos.id')
                                  ->get()
                                  ->pluck('id')->toArray();

                AccountInfo::whereIn('id', $ids)->delete();
                $ok = false;
            }


            $client = Client::where('cif', (string)$row[1])->first();
            if (!$client) {
                $client = Client::firstOrCreate([
                                                    'cif'           => (string)$row[1],
                                                    'branch_id'     => Branch::firstOrCreate(['name' => $row[2]])->id,
                                                    'class_type_id' => ClassType::firstOrCreate(['name' => $row[3]])->id,
                                                    'name'          => $row[5],
                                                ]);
            } else if (request()->replace == true and $client) {
                $client->update([
                                    'branch_id'     => Branch::firstOrCreate(['name' => $row[2]])->id,
                                    'class_type_id' => ClassType::firstOrCreate(['name' => $row[3]])->id,
                                    'name'          => $row[5],
                                ]);
            }


            $account = $client->clientAccounts()->firstOrCreate([
                                                                    'loan_key'              => (string)$row[0],
                                                                    'type_id'               => Type::firstOrCreate(['name' => $row[4]])->id,
                                                                    'main_currency_id'      => Currency::firstOrCreate(['name' => $row[7]])->id,
                                                                    'guarantee_currency_id' => $row[18] ? Currency::firstOrCreate(['name' => $row[18]])->id : null,
                                                                ]);


            $account->accountInfos()->firstOrCreate(
                [
                    'year'                                             => request()->year,
                    'quarter'                                          => request()->quarter,
                    'outstanding_fcy'                                  => (double)$row[8],
                    'outstanding_lcy'                                  => (double)$row[9],
                    'accrued_interest_lcy'                             => (double)$row[10],
                    'suspended_lcy'                                    => (double)$row[11],
                    'interest_received_in_advance_lcy'                 => (double)$row[12],
                    'st_date'                                          => Carbon::instance(Date::excelToDateTimeObject($row[13])),
                    'mat_date'                                         => Carbon::instance(Date::excelToDateTimeObject($row[14])),
                    'sp_date'                                          => Carbon::instance(Date::excelToDateTimeObject($row[15])),
                    'past_due_days'                                    => $row[16],
                    'number_of_reschedule'                             => $row[17],
                    'cm_guarantee'                                     => (double)$row[19],
                    'estimated_value_of_stock_collateral'              => (double)$row[20],
                    'pv_securities_guarantees'                         => (double)$row[21],
                    'mortgages'                                        => (double)$row[22],
                    'estimated_value_of_real_estate_collateral'        => (double)$row[23],
                    '80_per_estimated_value_of_real_estate_collateral' => (double)$row[24],
                    'pv_re_guarantees'                                 => (double)$row[25],
                    'interest_rate'                                    => (double)$row[26],
                    'pay_method'                                       => $row[27],
                    'number_of_installments'                           => $row[28],

                ]
            );
        }
    }
}
