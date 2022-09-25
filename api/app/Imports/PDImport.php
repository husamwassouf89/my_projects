<?php

namespace App\Imports;

use App\Models\Client\Grade;
use App\Models\PD\PD;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PDImport implements ToCollection
{
    private $pd;
    private $gradesCount;

    public function __construct(PD $pd)
    {
        $this->pd          = $pd;
        $this->gradesCount = $pd->classType->grades->count();
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $mpRow = [];
        $mpCol = [];

        foreach ($collection as $key => $row) {
            foreach ($row as $key2 => $value) {
                $value = trim($value);
                if ($key == 0 and $key2 >= 1 and $key2 < count($row)) {
                    $mpCol[$value] = Grade::where('class_type_id', $this->pd->class_type_id)->where('name', $value)->first()->id;
                }
                if ($key2 == 0 and $key >= 1 and $key < count($collection)) {
                    $mpRow[$value] = Grade::where('class_type_id', $this->pd->class_type_id)->where('name', $value)->first()->id;
                }
            }
        }


        foreach ($collection as $key => $row) {
            foreach ($row as $key2 => $value) {
                $value = trim($value);
                if ($key == 0 or $key2 == 0) continue;
                if ($key > $this->gradesCount or $key2 > $this->gradesCount) continue;

                $this->pd->values()->create(
                    [
                        'value'     => (double)$value,
                        'column_id' => $mpCol[trim($collection[0][$key2])],
                        'row_id'    => $mpRow[trim($collection[$key][0])],
                    ]
                );
            }
        }

    }
}
