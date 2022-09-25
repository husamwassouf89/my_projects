<?php

namespace App\Imports;

use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\Currency;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class LimitsImport implements ToCollection
{

    private $year, $quarter;

    public function __construct($year, $quarter)
    {
        $this->year    = $year;
        $this->quarter = $quarter;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $row) {
            if ($key == 0) continue;
            if (!$row[0] or !$row[2]) continue;

            $client = Client::where('cif', (string)$row[0])->first();
            if (!$client) {
                $client = Client::firstOrCreate([
                                                    'cif'           => (string)$row[0],
                                                    'class_type_id' => ClassType::firstOrCreate(['name' => $row[2]])->id,
                                                    'name'          => $row[1],
                                                ]);
            }


            $client->limits()->firstOrCreate(
                [
                    'general_limit_lcy'           => (double)$row[3] ?? 0,
                    'direct_limit_currency_id'    => $row[4] ? Currency::firstOrCreate(['name' => $row[4]])->id : null,
                    'direct_limit_lcy'            => (double)$row[5] ?? 0,
                    'un_direct_limit_currency_id' => $row[6] ? Currency::firstOrCreate(['name' => $row[6]])->id : null,
                    'un_direct_limit_lcy'         => (double)$row[7] ?? 0,
                    'cancellable'                 => strtolower($row[8]),
                    'year'                        => $this->year,
                    'quarter'                     => $this->quarter
                ]
            );
        }
    }
}
