<?php

namespace App\Imports;

use App\Models\Client\AccountInfo;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\ClientAccount;
use App\Models\Client\Currency;
use App\Models\Client\Grade;
use App\Models\Client\Type;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BankImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $ok = true;

        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
//            foreach ($row as $key1 => $val) {
//                $row[$key1] = trim($row[$key1]);
//            }
            if (!$row[1] or !$row[3] or (!$row[4] and !$row[6])) continue;
            if ($row[4]) {
                $classType = ClassType::firstOrCreate(['name' => $row[4]]);
            } else {
                $classType = ClassType::firstOrCreate(['name' => $row[6]]);
            }

            if (request()->replace == true and $ok == true) {
                $ids = AccountInfo::join('client_accounts', 'account_infos.client_account_id', '=', 'client_accounts.id')
                                  ->join('clients', 'client_accounts.client_id', '=', 'clients.id')
                                  ->join('class_types', 'class_types.id', '=', 'clients.class_type_id')
                                  ->where('class_types.category', '=', 'financial')
                                  ->whereNotIn('client_accounts.type_id', [9, 10, 11])
                                  ->where('account_infos.year', '=', request()->year)
                                  ->where('account_infos.quarter', '=', request()->quarter)
                                  ->select('account_infos.id')
                                  ->get()
                                  ->pluck('id')->toArray();

                AccountInfo::whereIn('id', $ids)->delete();

                $accountIds = AccountInfo::select('client_account_id')->get()->pluck('client_account_id')->toArray();
                ClientAccount::whereNotIn('id', $accountIds)->delete();

                $ok = false;
            }

            $gradeSerialNo = Grade::where('class_type_id', $classType->id)->count();
            $client        = Client::where('cif', (string)$row[2])->first();
            if (!$client) {
                $client = Client::firstOrCreate([
                                                    'cif'           => (string)$row[2],
                                                    'country'       => $row[3],
                                                    'class_type_id' => $classType->id,
                                                    'name'          => $row[1],
                                                    'grade_id'      => $row[5] ? Grade::firstOrCreate(['class_type_id' => $classType->id,
                                                                                                       'name'          => $row[5],
                                                                                                       'serial_no' => $gradeSerialNo])->id : null,
                                                ]);
            } else if (request()->replace == true and $client) {
                $client->update([
                                    'country'       => $row[3],
                                    'class_type_id' => $classType->id,
                                    'name'          => $row[1],
                                    'grade_id'      => $row[5] ? Grade::firstOrCreate(['class_type_id' => $classType->id,
                                                                                       'name'          => $row[5],
                                                                                       'serial_no' => $gradeSerialNo])->id : null,
                                ]);
            }


            $account = $client->clientAccounts()
                              ->firstOrCreate([
                                                  'loan_key'         => (string)$row[0],
                                                  'type_id'          => Type::firstOrCreate(['name' => $row[6]])->id,
                                                  'main_currency_id' => Currency::firstOrCreate(['name' => $row[7]])->id,
                                              ]);

            $account->accountInfos()->firstOrCreate(
                [
                    'year'                             => request()->year,
                    'quarter'                          => request()->quarter,
                    'outstanding_fcy'                  => (double)$row[8],
                    'outstanding_lcy'                  => (double)$row[9],
                    'st_date'                          => Carbon::instance(Date::excelToDateTimeObject($row[10])),
                    'mat_date'                         => Carbon::instance(Date::excelToDateTimeObject($row[11])),
                    'accrued_interest_lcy'             => (double)$row[12],
                    'interest_received_in_advance_lcy' => (double)$row[13],
                    'interest_rate'                    => (double)$row[14],
                    'number_of_installments'           => $row[15],
                ]
            );
        }
    }
}
