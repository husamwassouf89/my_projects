<?php

namespace App\Imports;

use App\Models\PD\PD;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PDImport implements ToCollection
{
    private $pd;

    public function __construct(PD $pd)
    {
        $this->$pd = $pd;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $this->pd->values()->create();
    }
}
