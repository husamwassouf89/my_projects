<?php

namespace App\Exports;

use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\DocumentType;
use App\Models\Staging\Stage;
use App\Services\ClientService;
use App\Traits\ExcelKit;
use App\Traits\HelpKit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EadGuaranteeExport implements WithEvents
//    , WithHeadings
{
    use ExcelKit, HelpKit;

    private $total, $stages, $leftColTotal, $rightColTotal, $classTypes, $diff;
    private $styleArray
        = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

    private $q, $year, $type, $limits, $category, $date1, $date2;

    public function __construct($q, $year, $type, $limits, $category)
    {
        $this->stages         = Stage::all();
        $this->classTypes     = ClassType::where('category', 'facility')->get();
        $this->total[0]       = 0;
        $this->ead            = $this->fillColZeros();
        $this->totalGaurantee = $this->fillColZeros();
        $this->diff           = $this->fillColZeros();

        $this->q        = $q;
        $this->year     = $year;
        $this->type     = $type;
        $this->limits   = $limits;
        $this->category = $category;

        $temp        = $this->getDateRange($this->year, $this->q);
        $this->date1 = $temp['first_date'];
        $this->date2 = $temp['last_date'];

        $this->date1 = Carbon::createFromTimeString($this->date1);
        $this->date2 = Carbon::createFromTimeString( $this->date2);


        $this->guarantee_type = ['cm_guarantee', 'pv_securities_guarantees', 'pv_re_guarantees'];

    }

    private function fillColZeros()
    {
        return array_fill(0, count($this->classTypes) + 1, 0);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $sheet->setCellValue('A1', 'ON BALANCE');
                $this->setBold($event, 'A1');
                $this->add($sheet, 'ead', 'on', 0, $event);
                $this->add($sheet, 'cm_guarantee', 'on', 1, $event, false);
                $this->add($sheet, 'pv_securities_guarantees', 'on', 2, $event, false);
                $this->add($sheet, 'pv_re_guarantees', 'on', 3, $event, false);
                $this->addDataColumn($sheet, 'total guarantee', $this->totalGaurantee, 4, $event);
                $this->calcDiff();
                $this->addDataColumn($sheet, 'net ead after guarantee', $this->diff, 5, $event);
                $this->add($sheet, 'ecl', 'on', 5, $event, false);


                $sheet->setCellValue('A8', 'OFF BALANCE');
                $this->setBold($event, 'A8');
                $this->add($sheet, 'ead', 'off', 0, $event, true, true, 7,);
                $this->add($sheet, 'cm_guarantee', 'off', 1, $event, false, true, 7);
                $this->add($sheet, 'pv_securities_guarantees', 'off', 2, $event, false, true, 7);
                $this->add($sheet, 'pv_re_guarantees', 'off', 3, $event, false);
                $this->addDataColumn($sheet, 'total guarantee', $this->totalGaurantee, 4, $event, true, true, 7);
                $this->calcDiff();
                $this->addDataColumn($sheet, 'net ead after guarantee', $this->diff, 5, $event, true, true, 7);
                $this->add($sheet, 'ecl', 'off', 5, $event, false, true, 7);


                $cellRange = 'A1:ZZ100'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($this->styleArray);
            },
        ];
    }

    function setBold($event, $range)
    {
        $event->sheet->getDelegate()->getStyle($range)->getFont()->setBold(true);
    }

    private function add($sheet, $disclosure, $balance, $base = 0, $event = null, $printSide = true, $shiftDown = false, $baseDown = 0)
    {
        if ($shiftDown) {
            $startRowFirst  = $baseDown + 1;
            $startRowSecond = $baseDown + 2;
        } else {
            $startRowFirst  = 1;
            $startRowSecond = 2;
        }
        $startColNumber = $base + 1;

        $startCol = $this->map[$startColNumber];

        $sheet->setCellValue($this->map[$startColNumber + 1] . $startRowFirst, strtoupper($disclosure));
        $this->setBold($event, $this->map[$startColNumber + 1] . $startRowFirst);


        // ****************** Facilities Part ***************************

        $facilityTotal[0] = 0;
        $currentRow       = null;
        foreach ($this->classTypes as $key => $item) {
            if ($printSide) $sheet->setCellValue($startCol . ($key + $startRowSecond), $item->name);
            if ($balance == 'of') {
                $types = DocumentType::$OFF_BALANCE_DOCUMENTS;
            } else {
                $types = null;
            }
            $data = $this->getClientsData($item->id, $disclosure, $types);
//            dd($data);
            foreach ($data as $key2 => $info) {
                $startColNumber = array_search($startCol, $this->map);

                $currentColNumber     = (int)$startColNumber + $key2 + 1;
                $currentRow           = ($key + $startRowSecond);
                $facilityTotal[$key2] += $info;
                if ($disclosure == 'ead') {
                    $this->ead[$key2] = $info;
                }
                if (in_array($disclosure, $this->guarantee_type)) {
                    $this->totalGaurantee[$key2] += $facilityTotal[$key2];
                }
                $sheet->setCellValue($this->map[$currentColNumber] . $currentRow, $info);
            }
        }
        if ($disclosure == 'ead') {
            $this->ead[count($this->classTypes)] = $facilityTotal[0];
        }
        if (in_array($disclosure, $this->guarantee_type)) {
            $this->totalGaurantee[count($this->classTypes)] += $facilityTotal[0];
        }

//        dd($facilityTotal);
        $this->totalRow($sheet, $event, $facilityTotal, $startCol, $currentRow, 'Total', $printSide);


    }

    public function getClientsData($classTypeId, $disclosure = 'outstanding_lcy', $documentType = null)
    {
        $ids = Client::query();
        $ids->allJoins();
        if (is_array($classTypeId)) {
            $ids->whereIn('class_type_id', $classTypeId);
        } else {
            $ids->where('class_type_id', $classTypeId);
        }
        $balance = 'on';
        if ($documentType) {

            $ids->whereIn('types.name', $documentType);
            $balance = 'off';
        }
        $ids->select('clients.id');
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

                    if (!in_array($disclosure, $this->guarantee_type)) {
                        $client[$disclosure] += $info[$disclosure];
                    } else {
                        $client[$disclosure] = $info[$disclosure];
                    }
                    $client->stage = $info->stage;
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
        }
        array_push($dataRaw, $total);
        return $dataRaw;
    }

    private function totalRow($sheet, $event, $totalData, $startCol, $currentRow, $label = 'total', $print = true)
    {
        if ($print) {
            $sheet->setCellValue($startCol . ($currentRow + 1), $label);
            $this->setBold($event, $startCol . ($currentRow + 1));
        }

        foreach ($totalData as $key2 => $info) {

            $this->total[$key2] += $info;

            $startColNumber   = array_search($startCol, $this->map);
            $currentColNumber = (int)$startColNumber + $key2 + 1;
            $sheet->setCellValue($this->map[$currentColNumber] . ($currentRow + 1), $info);
            $this->setBold($event, $this->map[$currentColNumber] . ($currentRow + 1));
        }
    }

    private function addDataColumn($sheet, $label, $data, $base = 0, $event = null, $shiftRight = true, $shiftDown = false, $baseDown = 0)
    {
        if ($shiftDown) {
            $startRowFirst  = $baseDown + 1;
            $startRowSecond = $baseDown + 1;
        } else {
            $startRowFirst  = 1;
            $startRowSecond = 1;
        }
        if ($shiftRight) {
            $startColNumber = $base + 1;
        } else {
            $startColNumber = 1;
        }

        $startCol = $this->map[$startColNumber];
        $sheet->setCellValue($startCol . $startRowFirst, strtoupper($label));
        $this->setBold($event, $startCol . $startRowFirst);
        foreach ($data as $key => $item) {
            $currentRowNumber = $startRowSecond + $key + 1;
            if ($key != count($data) - 1) {
                $sheet->setCellValue($startCol . $currentRowNumber, (string)$data[$key]);

            } else {
                $sheet->setCellValue($startCol . ($currentRowNumber), (string)$data[$key]);
                $this->setBold($event, $startCol . ($currentRowNumber));

            }
        }
    }

    private function calcDiff()
    {
        foreach ($this->diff as $key => $item) {
            $this->diff[$key] = $this->ead[$key] - $this->totalGaurantee[$key];
        }

    }


}
