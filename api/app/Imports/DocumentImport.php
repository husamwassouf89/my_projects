<?php

namespace App\Imports;

use App\Models\Client\AccountInfo;
use App\Models\Client\Branch;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\ClientAccount;
use App\Models\Client\Currency;
use App\Models\Client\DocumentType;
use App\Models\Client\Type;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DocumentImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $ok = true;
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
            if (!$row[3] or !$row[4]) continue;


            if (request()->replace == true and $ok == true) {
                $id  = ClassType::where('name', $row[4])->first()->id;
                $ids = AccountInfo::join('client_accounts', 'account_infos.client_account_id', '=', 'client_accounts.id')
                                  ->join('clients', 'client_accounts.client_id', '=', 'clients.id')
                                  ->where('clients.class_type_id', '=', $id)
                                  ->whereIn('client_accounts.type_id', [9, 10, 11])
                                  ->where('account_infos.year', '=', request()->year)
                                  ->where('account_infos.quarter', '=', request()->quarter)
                                  ->select('account_infos.id')
                                  ->get();


                AccountInfo::whereIn('id', $ids->pluck('id')->toArray())->delete();
                ClientAccount::whereIn('id', $ids->pluck('client_account_id')->toArray())->delete();

                $ok = false;
            }


            $client = Client::where('cif', (string)$row[3])->first();
            if (!$client) {
                $client = Client::firstOrCreate([
                                                    'cif'           => (string)$row[3],
                                                    'class_type_id' => ClassType::firstOrCreate(['name' => $row[4]])->id,
                                                    'name'          => $row[2],
                                                ]);
            } else if (request()->replace == true and $client) {
                $client->update([
                                    'class_type_id' => ClassType::firstOrCreate(['name' => $row[4]])->id,
                                    'name'          => $row[2],
                                ]);
            }


            $account = $client->clientAccounts()
                              ->firstOrCreate([
                                                  'loan_key'              => (string)$row[0],
                                                  'type_id'               => Type::firstOrCreate(['name' => $row[1]])->id,
                                                  'document_type_id'      => DocumentType::firstOrCreate(['name' => $row[8]])->id,
                                                  'main_currency_id'      => Currency::firstOrCreate(['name' => $row[5]])->id,
                                                  'guarantee_currency_id' => $row[11] ? Currency::firstOrCreate(['name' => $row[11]])->id : null,
                                              ]);

            $account->accountInfos()->firstOrCreate(
                [
                    'year'            => request()->year,
                    'quarter'         => request()->quarter,
                    'outstanding_fcy' => (double)$row[6],
                    'outstanding_lcy' => (double)$row[7],
                    'st_date'         => Carbon::instance(Date::excelToDateTimeObject($row[9])),
                    'mat_date'        => Carbon::instance(Date::excelToDateTimeObject($row[10])),
                    'cm_guarantee'    => (double)$row[12],

                ]
            );
        }
    }
}
