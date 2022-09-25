<?php

namespace App\Exports;

use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\DocumentType;
use App\Models\Staging\Stage;
use App\Services\ClientService;
use App\Traits\HelpKit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DisclosuresExport implements WithEvents
//    , WithHeadings
{

    use HelpKit;

    private $total, $stages, $leftColTotal, $rightColTotal, $temp;
    private $styleArray
        = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
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

//    private $q1, $q2, $year1, $year2, $type, $limits, $category, $date1, $date2;
    private $q, $year, $type, $limits, $category, $date1, $date2;

    public function __construct($q, $year, $type, $limits, $category)
    {
        $this->stages        = Stage::all();
        $this->total         = $this->fillZeros();
        $this->leftColTotal  = [];
        $this->rightColTotal = [];
        $this->temp          = [];

        $this->q        = $q;
        $this->year     = $year;
        $this->type     = $type;
        $this->limits   = $limits;
        $this->category = $category;

        $temp        = $this->getDateRange($this->year, $this->q);
        $this->date1 = $temp['first_date'];
        $this->date2 = $temp['last_date'];

        $this->date1 = Carbon::createFromTimeString($this->date1);
        $this->date2 = Carbon::createFromTimeString($this->date2);
    }

    private function fillZeros()
    {
        return array_fill(0, count($this->stages) + 1, 0);
    }

    public function registerEvents(): array
    {

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $this->add($sheet, 'outstanding_lcy', 0, $event, null);
                $this->total = $this->fillZeros();
                $this->add($sheet, 'ead', 6, $event, 'left');
                $this->total = $this->fillZeros();
                $this->add($sheet, 'ecl', 13, $event, 'right');
                $this->total = $this->fillZeros();
                $this->addDataColumn($sheet, 20, $event, 'left');


                $cellRange = 'A1:ZZ100'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($this->styleArray);
            },
        ];
    }

    private function add($sheet, $disclosure, $base = 0, $event = null, $colType = null, $shiftRight = true, $shiftDown = false)
    {
        if ($shiftDown) {
            $startRowFirst  = $base + 1;
            $startRowSecond = $base + 3;
        } else {
            $startRowFirst  = 1;
            $startRowSecond = 3;
        }
        if ($shiftRight) {
            $startColNumber = $base + 1;
        } else {
            $startColNumber = 1;
        }
        $startCol = $this->map[$startColNumber];

        $stages           = Stage::all();
        $outstandingRange = $this->generateRange($startCol, $startRowFirst, count($stages) + 1);
        $sheet->mergeCells($outstandingRange);
        $sheet->setCellValue($startCol . $startRowFirst, strtoupper($disclosure));
        $this->setBold($event, $startCol . $startRowFirst);


        foreach ($stages as $key => $item) {
            $sheet->setCellValue($this->map[$startColNumber + $key + 1] . ($startRowFirst + 1), $item->name);
        }
        $sheet->setCellValue($this->map[$startColNumber + count($stages) + 1] . ($startRowFirst + 1), 'Total');

        // ****************** Facilities Part ***************************
        $classTypes    = ClassType::where('category', 'facility')->get();
        $facilityTotal = $this->fillZeros();
        $currentRow    = null;
        foreach ($classTypes as $key => $item) {
            $sheet->setCellValue($startCol . ($key + $startRowSecond), $item->name);
            $data = $this->getClientData($item->id, $disclosure, null, null, 'on', $colType);
            foreach ($data as $key2 => $info) {
                $startColNumber = array_search($startCol, $this->map);

                $currentColNumber     = (int)$startColNumber + $key2 + 1;
                $currentRow           = ($key + $startRowSecond);
                $facilityTotal[$key2] += $info;
                $sheet->setCellValue($this->map[$currentColNumber] . $currentRow, $info);
            }
        }
        $this->totalRow($sheet, $event, $facilityTotal, $startCol, $currentRow, 'Loan and advance', $colType);

        // ****************** Central Bank Part ***************************
        $currentRow += 1;

        $classType = ClassType::where('id', 8)->first();
        $data      = $this->getClientData($classType->id, $disclosure, null, null);
        $this->totalRow($sheet, $event, $data, $startCol, $currentRow, $classType->name, $colType);

        // ****************** Internal Bank Part ***************************
        $currentRow += 2;

        $classType         = ClassType::where('id', 5)->first();
        $types             = ['Nostro', 'Placements'];
        $internalBankTotal = $this->fillZeros();

        foreach ($types as $type) {
            $sheet->setCellValue($startCol . $currentRow, $type);
            $data = $this->getClientData($classType->id, $disclosure, $type, null, 'on', $colType);
            foreach ($data as $key2 => $info) {
                $startColNumber           = array_search($startCol, $this->map);
                $currentColNumber         = (int)$startColNumber + $key2 + 1;
                $internalBankTotal[$key2] += $info;
                $sheet->setCellValue($this->map[$currentColNumber] . $currentRow, $info);
            }
            $currentRow += 1;
        }
        $currentRow -= 1;
        $this->totalRow($sheet, $event, $internalBankTotal, $startCol, $currentRow, $classType->name, $colType);


        // ****************** External Bank Part ***************************

        $currentRow        += 2;
        $classType         = ClassType::where('id', 6)->first();
        $externalBankTotal = $this->fillZeros();

        foreach ($types as $type) {
            $sheet->setCellValue($startCol . $currentRow, $type);
            $data = $this->getClientData($classType->id, $disclosure, $type, null, 'on', $colType);
            foreach ($data as $key2 => $info) {
                $startColNumber           = array_search($startCol, $this->map);
                $currentColNumber         = (int)$startColNumber + $key2 + 1;
                $externalBankTotal[$key2] += $info;
                $sheet->setCellValue($this->map[$currentColNumber] . $currentRow, $info);
            }
            $currentRow += 1;
        }
        $currentRow -= 1;
        $this->totalRow($sheet, $event, $externalBankTotal, $startCol, $currentRow, $classType->name, $colType);
        $banksSum = [];
        foreach ($internalBankTotal as $key3 => $item) {
            array_push($banksSum, $internalBankTotal[$key3] + $externalBankTotal[$key3]);
        }

        $currentRow += 1;
        $this->totalRow($sheet, $event, $banksSum, $startCol, $currentRow, 'Current account&placement with bank', $colType);

//         ****************** Off Balance Sheet Part ***************************
        $currentRow      += 2;
        $classTypesId    = ClassType::select('id')->get()->pluck('id')->toArray();
        $types           = DocumentType::$OFF_BALANCE_DOCUMENTS;
        $offBalanceTotal = $this->fillZeros();

        foreach ($types as $type) {
            $sheet->setCellValue($startCol . $currentRow, $type);
            $data = $this->getClientData($classTypesId, $disclosure, null, $type, 'off', $colType);
            foreach ($data as $key2 => $info) {
                $startColNumber         = array_search($startCol, $this->map);
                $currentColNumber       = (int)$startColNumber + $key2 + 1;
                $offBalanceTotal[$key2] += $info;
                $sheet->setCellValue($this->map[$currentColNumber] . $currentRow, $info);
            }
            $currentRow += 1;
        }
        $currentRow -= 1;
        $this->totalRow($sheet, $event, $offBalanceTotal, $startCol, $currentRow, 'Off Balance Sheet', $colType);

        $currentRow += 3;
        $this->totalRow($sheet, $event, $this->total, $startCol, $currentRow, 'Total', $colType);

    }

    private function generateRange($startRow, $startCol, $range, $type = 'horizontal')
    {
        if ($type == 'vertical') {
            $finalRange = $startRow . $startCol . ':' . $startRow . ($startCol + $range - 1);
        } else {
            $rowNumber  = (int)array_search($startRow, $this->map);
            $finalRange = $startRow . $startCol . ':' . $this->map[$rowNumber + $range - 1] . $startCol;
        }
        return $finalRange;
    }

    private function setBold($event, $range)
    {
        $event->sheet->getDelegate()->getStyle($range)->getFont()->setBold(true);
    }

    public function getClientData($classTypeId, $disclosure = 'outstanding_lcy', $type = null, $documentType = null, $balance = 'on', $colType = null)
    {
        $ids = Client::query();
        $ids->allJoins();
        if (is_array($classTypeId)) {
            $ids->whereIn('class_type_id', $classTypeId);
        } else {
            $ids->where('class_type_id', $classTypeId);
        }
        if (!$documentType and $type) {
            $ids->where('types.name', $type);
        } else if ($documentType) {
            $ids->where('document_types.name', $documentType);
        }
        $ids->select('clients.id');
        if ($this->category) {
            $ids->where('class_types.category', $this->category);
        }
        $ids = $ids->distinct('id')->get()->pluck('id')->toArray();

        $clients       = [];
        $clientService = new ClientService();
        $stages        = Stage::all();
        foreach ($ids as $id) {
            $client = $clientService->show($id, $balance);
            $ok     = true;
            foreach ($client->clientAccounts as $account) {
                foreach ($account->accountInfos as $info) {

                    $firstDate  = $this->getDateRange($info->year, $info->quarter)['first_date'];
                    $secondDate = $this->getDateRange($info->year, $info->quarter)['last_date'];
                    $firstDate = Carbon::createFromTimeString($firstDate);
                    $secondDate = Carbon::createFromTimeString($secondDate);
                    if (!($firstDate<= $this->date1 and $secondDate <= $this->date2)) {
                        continue;
                    }

                    $client[$disclosure] += $info[$disclosure];
                    $client->stage       = $info->stage;
                    if (!$info->final_grade or !is_numeric($info->pd) or !$info->stage) $ok = false;
                }
            }
            if ($ok) {
                $clientData = [];

                foreach ($stages as $stage) {
                    $value                    = $stage->name == $client->stage ? $client[$disclosure] : 0;
                    $clientData[$stage->name] = $value;
                }

                array_push($clients, $clientData);
            }
        }
        $stagesValue = [];

        foreach ($stages as $stage) {
            if (!isset($stagesValue[$stage->name])) $stagesValue[$stage->name] = 0;
            foreach ($clients as $client) {
                $stagesValue[$stage->name] += $client[$stage->name];
            }
        }
        $dataRaw = [];
        $total   = 0;
        foreach ($stages as $stage) {
            $total += $stagesValue[$stage->name];
            array_push($dataRaw, $stagesValue[$stage->name]);
        }

        array_push($dataRaw, $total);
        array_push($this->temp, 1);

        if ($colType == 'left') {
            array_push($this->leftColTotal, $total);
        } else if ($colType == 'right') {
            array_push($this->rightColTotal, $total);
        }
        return $dataRaw;
    }

    private function totalRow($sheet, $event, $totalData, $startCol, $currentRow, $label = 'total', $colType = null)
    {
        $sheet->setCellValue($startCol . ($currentRow + 1), $label);
        $this->setBold($event, $startCol . ($currentRow + 1));


        foreach ($totalData as $key2 => $info) {

            $this->total[$key2] += $info;

            $startColNumber   = array_search($startCol, $this->map);
            $currentColNumber = (int)$startColNumber + $key2 + 1;
            $sheet->setCellValue($this->map[$currentColNumber] . ($currentRow + 1), $info);
            $this->setBold($event, $this->map[$currentColNumber] . ($currentRow + 1));
        }
        if ($colType == 'left') {
            array_push($this->leftColTotal, $totalData[count($this->stages)]);
        } else if ($colType == 'right') {
            array_push($this->rightColTotal, $totalData[count($this->stages)]);
        }

    }

    private function addDataColumn($sheet, $base = 0, $event = null, $colType = null, $shiftRight = true, $shiftDown = false)
    {
        if ($shiftDown) {
            $startRowFirst  = $base + 1;
            $startRowSecond = $base + 2;
        } else {
            $startRowFirst  = 1;
            $startRowSecond = 2;
        }
        if ($shiftRight) {
            $startColNumber = $base + 1;
        } else {
            $startColNumber = 1;
        }
        $startCol = $this->map[$startColNumber];

        $label = 'Net Exposure';

        $sheet->setCellValue($startCol . $startRowFirst, strtoupper($label));
        $this->setBold($event, $startCol . $startRowFirst);
        foreach ($this->rightColTotal as $key => $item) {
            $currentRowNumber = $startRowSecond + $key + 1;
            if ($key != count($this->rightColTotal) - 1) {
                $sheet->setCellValue($startCol . $currentRowNumber, $this->leftColTotal[$key] - $this->rightColTotal[$key]);

            } else {
                $sheet->setCellValue($startCol . ($currentRowNumber + 2), $this->leftColTotal[$key] - $this->rightColTotal[$key]);
                $this->setBold($event, $startCol . ($currentRowNumber + 2));

            }
        }

        $startColNumber += 2;
        $startCol       = $this->map[$startColNumber];

        $label = '% Provisions';

        $sheet->setCellValue($startCol . $startRowFirst, strtoupper($label));
        $this->setBold($event, $startCol . $startRowFirst);
        foreach ($this->rightColTotal as $key => $item) {
            $currentRowNumber = $startRowSecond + $key + 1;
            if ($key != count($this->rightColTotal) - 1) {
                $sheet->setCellValue($startCol . $currentRowNumber, $this->leftColTotal[$key] ? $this->rightColTotal[$key] / $this->leftColTotal[$key] : '-');

            } else {
                $sheet->setCellValue($startCol . ($currentRowNumber + 2), $this->leftColTotal[$key] ? $this->rightColTotal[$key] / $this->leftColTotal[$key] : '-');
                $this->setBold($event, $startCol . ($currentRowNumber + 2));


            }
        }


    }


}
