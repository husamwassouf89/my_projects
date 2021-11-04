<?php

namespace App\Imports;

use App\Models\Client\ClassType;
use App\Models\Client\Client;
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
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
            if ($row[4]) {
                $classType = ClassType::firstOrCreate(['name' => $row[4]]);
            } else {
                $classType = ClassType::firstOrCreate(['name' => $row[6]]);

            }

            $gradeSerialNo = Grade::where('class_type_id', $classType->id)->count();

            $client = Client::firstOrCreate([
                                                'cif'           => $row[2],
                                                'country'       => $row[5],
                                                'class_type_id' => $classType->id,
                                                'name'          => $row[1],
                                                'grade_id'      => $row[5] ? Grade::firstOrCreate(['serial_no' => $gradeSerialNo + 1, 'class_type_id' => $classType->id, 'name' => $row[5]])->id : null,
                                            ]);


            $account = $client->clientAccounts()
                              ->firstOrCreate([
                                                  'loan_key'         => $row[0],
                                                  'type_id'          => Type::firstOrCreate(['name' => $row[6]])->id,
                                                  'main_currency_id' => Currency::firstOrCreate(['name' => $row[7]])->id,
                                              ]);

            $account->accountInfos()->firstOrCreate(
                [
                    'year'                             => request()->year,
                    'quarter'                          => request()->quarter,
                    'outstanding_fcy'                  => $row[8],
                    'outstanding_lcy'                  => $row[9],
                    'st_date'                          => Carbon::instance(Date::excelToDateTimeObject($row[10])),
                    'mat_date'                         => Carbon::instance(Date::excelToDateTimeObject($row[11])),
                    'accrued_interest_lcy'             => $row[12],
                    'interest_received_in_advance_lcy' => $row[13],
                    'interest_rate'                    => (double)$row[14],
                    'number_of_installments'           => $row[15],
                ]
            );
        }
    }
}
