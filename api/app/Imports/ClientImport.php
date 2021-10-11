<?php

namespace App\Imports;

use App\Models\Client\Branch;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\Currency;
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
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
            $client = Client::firstOrCreate([
                                                'cif'           => $row[1],
                                                'branch_id'     => Branch::firstOrCreate(['name' => $row[2]])->id,
                                                'class_type_id' => ClassType::firstOrCreate(['name' => $row[3]])->id,
                                                'name'          => $row[5],
                                            ]);

            $account = $client->clientAccounts()->firstOrCreate([
                                                                    'loan_key'              => $row[0],
                                                                    'type_id'               => Type::firstOrCreate(['name' => $row[4]])->id,
                                                                    'main_currency_id'      => Currency::firstOrCreate(['name' => $row[7]])->id,
                                                                    'guarantee_currency_id' => $row[18]?Currency::firstOrCreate(['name' => $row[18]])->id:null,
                                                                ]);

            $account->accountInfos()->firstOrCreate(
                [
                    'year'                                             => request()->year,
                    'quarter'                                          => request()->quarter,
                    'outstanding_fcy'                                  => $row[8],
                    'outstanding_lcy'                                  => $row[9],
                    'accrued_interest_lcy'                             => $row[10],
                    'suspended_lcy'                                    => $row[11],
                    'interest_received_in_advance_lcy'                 => $row[12],
                    'st_date'                                          => Carbon::instance(Date::excelToDateTimeObject($row[13])),
                    'mat_date'                                         => Carbon::instance(Date::excelToDateTimeObject($row[14])),
                    'sp_date'                                          => Carbon::instance(Date::excelToDateTimeObject($row[15])),
                    'past_due_days'                                    => $row[16],
                    'number_of_reschedule'                             => $row[17],
                    'cm_guarantee'                                     => $row[19],
                    'estimated_value_of_stock_collateral'              => $row[20],
                    'pv_securities_guarantees'                         => $row[21],
                    'mortgages'                                        => $row[22],
                    'estimated_value_of_real_estate_collateral'        => $row[23],
                    '80_per_estimated_value_of_real_estate_collateral' => $row[24],
                    'pv_re_guarantees'                                 => $row[25],
                    'interest_rate'                                    => (double)$row[26],
                    'pay_method'                                       => $row[27],
                    'number_of_installments'                           => $row[28],

                ]
            );
        }
    }
}
