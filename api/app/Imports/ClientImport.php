<?php

namespace App\Imports;

use App\Models\Client\Client;
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
                               'cid'  => $row[0],
                               'name' => $row[1],
                           ]);
        }
    }
}
