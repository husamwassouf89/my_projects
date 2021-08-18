<?php

namespace App\Imports;

use App\Models\Client\Branch;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\Currency;
use App\Models\Client\Type;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ClientImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
            Client::create([
                               'loan_key'                                         => $row[0],
                               'cif'                                              => $row[1],
                               'branch_id'                                        => Branch::firstOrCreate(['name' => $row[2]])->id,
                               'class_type_id'                                    => ClassType::firstOrCreate(['name' => $row[3]])->id,
                               'type_id'                                          => Type::firstOrCreate(['name' => $row[4]])->id,
                               'name'                                             => $row[5],
                               'main_currency_id'                                 => Currency::firstOrCreate(['name' => $row[7]])->id,
                               'outstanding_fcy'                                  => $row[8],
                               'outstanding_lcy'                                  => $row[9],
                               'accrued_interest_lcy'                             => $row[10],
                               'suspended_lcy'                                    => $row[11],
                               'interest_received_in_advance_lcy'                 => $row[12],
                               'st_date'                                          => $row[13],
                               'mat_date'                                         => $row[14],
                               'sp_date'                                          => $row[15],
                               'past_due_days'                                    => $row[16],
                               'number_of_reschedule'                             => $row[17],
                               'guarantee_ccy'                                    => $row[18],
                               'cm_guarantee'                                     => $row[19],
                               'estimated_value_of_stock_collateral'              => $row[20],
                               'pv_securities_guarantees'                         => $row[21],
                               'mortgages'                                        => $row[22],
                               'estimated_value_of_real_estate_collateral'        => $row[23],
                               '80_per_estimated_value_of_real_estate_collateral' => $row[24],
                               'pv_re_guarantees'                                 => $row[25],
                               'interest_rate'                                    => $row[26],
                               'pay_method'                                       => $row[27],
                               'number_of_installments'                           => $row[28],
                           ]);
        }
    }
}
