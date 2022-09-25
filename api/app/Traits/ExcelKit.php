<?php

namespace App\Traits;

trait ExcelKit
{
    private $map
        = [
            1  => 'A', 2 => 'B', 3 => 'C',
            4  => 'D', 5 => 'E', 6 => 'F',
            7  => 'G', 8 => 'H', 9 => 'I',
            10 => 'J', 11 => 'K', 12 => 'L',
            13 => 'M', 14 => 'N', 15 => 'O',
            16 => 'P', 17 => 'Q', 18 => 'R',
            19 => 'S', 20 => 'T', 21 => 'U',
            22 => 'V', 23 => 'W', 24 => 'X',
            25 => 'Y', 26 => 'Z',
        ];

    function generateRange($startRow, $startCol, $range, $type = 'horizontal')
    {
        if ($type == 'vertical') {
            $finalRange = $startRow . $startCol . ':' . $startRow . ($startCol + $range - 1);
        } else {
            $rowNumber  = (int)array_search($startRow, $this->map);
            $finalRange = $startRow . $startCol . ':' . $this->map[$rowNumber + $range - 1] . $startCol;
        }
        return $finalRange;
    }

    function fillZeros()
    {
        return array_fill(0, count($this->stages) + 1, 0);
    }



}
