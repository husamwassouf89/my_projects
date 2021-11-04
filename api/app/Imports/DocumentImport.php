<?php

namespace App\Imports;

use App\Models\Client\ClassType;
use App\Models\Client\Client;
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
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
            $client = Client::firstOrCreate([
                                                'cif'           => $row[3],
                                                'class_type_id' => ClassType::firstOrCreate(['name' => $row[4]])->id,
                                                'name'          => $row[2],
                                            ]);

            $account = $client->clientAccounts()
                              ->firstOrCreate([
                                                  'loan_key'              => $row[0],
                                                  'type_id'               => Type::firstOrCreate(['name' => $row[1]])->id,
                                                  'document_type_id'      => DocumentType::firstOrCreate(['name' => $row[8]])->id,
                                                  'main_currency_id'      => Currency::firstOrCreate(['name' => $row[5]])->id,
                                                  'guarantee_currency_id' => $row[11] ? Currency::firstOrCreate(['name' => $row[11]])->id : null,
                                              ]);

            $account->accountInfos()->firstOrCreate(
                [
                    'year'            => request()->year,
                    'quarter'         => request()->quarter,
                    'outstanding_fcy' => $row[6],
                    'outstanding_lcy' => $row[7],
                    'st_date'         => Carbon::instance(Date::excelToDateTimeObject($row[9])),
                    'mat_date'        => Carbon::instance(Date::excelToDateTimeObject($row[10])),
                    'cm_guarantee'    => $row[12],

                ]
            );
        }
    }
}
