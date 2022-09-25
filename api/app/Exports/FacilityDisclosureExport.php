<?php

namespace App\Exports;

use App\Models\Client\Client;
use App\Services\ClientService;
use App\Traits\ExcelKit;
use App\Traits\HelpKit;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FacilityDisclosureExport implements WithEvents
{

    use HelpKit, ExcelKit;

    private $sheet, $q1, $q2, $year1, $year2, $firstData, $secondData, $currency, $secondPart, $thirdPart;
    private $stageMap
        = [
            'مرحلة 1' => 0,
            'مرحلة 2' => 1,
            'مرحلة 3' => 2,
        ];

    private $type, $limits, $category;

    public function __construct($q1, $year1, $q2, $year2, $type, $limits, $category)
    {
        $this->q1         = $q1;
        $this->q2         = $q2;
        $this->year1      = $year1;
        $this->year2      = $year2;
        $this->type       = $type;
        $this->limits     = $limits;
        $this->category   = $category;
        $this->currency   = 'IQD';
        $this->secondPart = array_fill(0, 9, array_fill(0, 4, 0));
        $this->thirdPart  = array_fill(0, 9, array_fill(0, 4, 0));
        $this->firstData  = $this->getClientsData();
        $this->secondData = $this->getClientsData('second');
        $this->handleNewClosed();

    }

    public function getClientsData($type = 'first')
    {
        if ($type == 'second') {
            $q    = $this->q2;
            $year = $this->year2;
        } else {
            $q    = $this->q1;
            $year = $this->year1;
        }

        $ids = Client::join('class_types', 'class_types.id', '=', 'clients.class_type_id')
                     ->where('class_types.category', $this->category)
                     ->select('clients.id')->get()->pluck('id')->toArray();


        $clients       = [];
        $clientService = new ClientService();
        foreach ($ids as $id) {
            $client = $clientService->showQuarter($id, $q, $year,$type=='documents'?'on':null,$this->limits);
            foreach ($client->clientAccounts as $account) {
                foreach ($account->accountInfos as $info) {
                    if (!$info->final_grade or !is_numeric($info->pd) or !$info->stage) continue;
                    array_push($clients, [$client->class_type_name, $client->cif, $info->stage, $info->outstanding_lcy, $info->ecl]);
                    break;
                }
            }
        }
        return $clients;
    }

    private function handleNewClosed()
    {
        $data1 = $this->firstData;
        $data2 = $this->secondData;
        foreach ($data1 as $item) {
            $cif = $item[1];
            $ok  = false;
            foreach ($data2 as $item2) {
                if ($cif == $item2[1]) $ok = true;
            }
            if (!$ok) {
                array_push($data2, [$item[0], $item[1], "Closed", 0, 0]);
            }
        }
        foreach ($data2 as $item) {
            $cif = $item[1];
            $ok  = false;
            foreach ($data1 as $item2) {
                if ($cif == $item2[1]) $ok = true;
            }
            if (!$ok) {
                array_push($data1, [$item[0], $item[1], "New", 0, 0]);
            }
        }
        $this->firstData  = $data1;
        $this->secondData = $data2;
        return true;
    }

    public function registerEvents(): array
    {

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet;
                $this->sheet = $sheet;
                $this->addHeader();
                $this->addData();

                $this->addSecondPartHeader();
                $this->addThirdPartHeader();
                $this->addSecondPartData();
                $this->addThirdPartData();

                $this->setStyle();
            },
        ];
    }

    public function addHeader()
    {
        $this->sheet->mergeCells('A2:A3');
        $this->sheet->setCellValue('A2', 'Portfolio');

        $this->sheet->mergeCells('B2:B3');
        $this->sheet->setCellValue('B2', 'CIF');

        $this->sheet->mergeCells('C2:D2');
        $this->sheet->setCellValue('C2', 'Stage');
        $this->sheet->setCellValue('C3', $this->getDate());
        $this->sheet->setCellValue('D3', $this->getDate('second'));

        $this->sheet->mergeCells('E2:F2');
        $this->sheet->setCellValue('E2', 'Outstanding');
        $this->sheet->setCellValue('E3', $this->getDate());
        $this->sheet->setCellValue('F3', $this->getDate('second'));

        $this->sheet->mergeCells('G2:G3');
        $this->sheet->setCellValue('G2', 'Change');

        $this->sheet->mergeCells('H2:H3');
        $this->sheet->setCellValue('H2', 'Provision-' . $this->getDate('second') . '/ON');
    }

    private function getDate($type = 'first')
    {
        if ($type == 'second') return $this->year2 . '-' . $this->q2;
        else return $this->year1 . '-' . $this->q1;
    }

    public function addData()
    {
        $data1 = $this->firstData;
        $data2 = $this->secondData;

        $startCol = 1;
        $rowNo    = 4;

        $total = [0, 0, 0, 0];

        $data = $data2;
        if (count($data1) >= count($data2)) $data = $data1;
        foreach ($data as $key => $row1) {
            $this->sheet->setCellValue($this->map[$startCol + 0] . $rowNo, $data1[$key][0]);
            $this->sheet->setCellValue($this->map[$startCol + 1] . $rowNo, $data1[$key][1]);
            $this->sheet->setCellValue($this->map[$startCol + 2] . $rowNo, $data1[$key][2]);
            $this->sheet->setCellValue($this->map[$startCol + 3] . $rowNo, $data2[$key][2]);
            $this->sheet->setCellValue($this->map[$startCol + 4] . $rowNo, $data1[$key][3]);
            $this->sheet->setCellValue($this->map[$startCol + 5] . $rowNo, $data2[$key][3]);
            $this->sheet->setCellValue($this->map[$startCol + 6] . $rowNo, $data2[$key][3] - $data1[$key][3]);
            $this->sheet->setCellValue($this->map[$startCol + 7] . $rowNo, $data2[$key][4]);

            $total[0] += $data1[$key][3];
            $total[1] += $data2[$key][3];
            $total[2] += $data2[$key][3] - $data1[$key][3];
            $total[3] += $data2[$key][4];
            $rowNo    += 1;


            // Second Part Total
            // for date
            $this->secondPart[0][$this->stageMap[$row1[2]]] += $data1[$key][3];
            if ($data1[$key][2] != 'New') {
                $this->secondPart[1][$this->stageMap[$data1[$key][2]]] += max($data2[$key][3] - $data1[$key][3], 0);
                $this->secondPart[2][$this->stageMap[$data1[$key][2]]] += ($data2[$key][3] - $data1[$key][3]) < 0 ? abs($data2[$key][3] - $data1[$key][3]) : 0;
            } else {
                $this->secondPart[1][$this->stageMap[$data2[$key][2]]] += max($data2[$key][3] - $data1[$key][3], 0);
            }


            if ($data2[$key][2] != 'Closed') {
                // Moved to Stage 1
                // From Stage 2&3 to Stage 1
                $this->secondPart[3][0] += ($this->stageMap[$data1[$key][2]] != 0 and $this->stageMap[$data2[$key][2]] == 0) ? max($data2[$key][3], 0) : 0;
                // From Stage 2 to Stage 1
                $this->secondPart[3][1] += ($this->stageMap[$data1[$key][2]] == 1 and $this->stageMap[$data2[$key][2]] == 0) ? max($data2[$key][3], 0) : 0;
                // From Stage 3 to Stage 1
                $this->secondPart[3][2] += ($this->stageMap[$data1[$key][2]] == 2 and $this->stageMap[$data2[$key][2]] == 0) ? max($data2[$key][3], 0) : 0;

                // Moved to Stage 2
                // From Stage 1 to Stage 2
                $this->secondPart[4][0] += ($this->stageMap[$data1[$key][2]] == 0 and $this->stageMap[$data2[$key][2]] == 1) ? max($data2[$key][3], 0) : 0;
                // From Stage 1&3 to Stage 2
                $this->secondPart[4][1] += ($this->stageMap[$data1[$key][2]] != 1 and $this->stageMap[$data2[$key][2]] == 1) ? max($data2[$key][3], 0) : 0;
                // From Stage 3 to Stage 2
                $this->secondPart[4][2] += ($this->stageMap[$data1[$key][2]] == 2 and $this->stageMap[$data2[$key][2]] == 1) ? max($data2[$key][3], 0) : 0;

                // Moved to Stage 3
                // From Stage 1 to Stage 3
                $this->secondPart[5][0] += ($this->stageMap[$data1[$key][2]] == 0 and $this->stageMap[$data2[$key][2]] == 2) ? max($data2[$key][3], 0) : 0;
                // From Stage 2 to Stage 3
                $this->secondPart[5][1] += ($this->stageMap[$data1[$key][2]] == 1 and $this->stageMap[$data2[$key][2]] == 2) ? max($data2[$key][3], 0) : 0;
                // From Stage 1&2 to Stage 3
                $this->secondPart[5][2] += ($this->stageMap[$data1[$key][2]] != 2 and $this->stageMap[$data2[$key][2]] == 2) ? max($data2[$key][3], 0) : 0;
            }


            // Third Part Total
            // for date
            $this->thirdPart[0][$this->stageMap[$row1[2]]] += max($data1[$key][3] ?? 0, 0);
            $this->thirdPart[1][$this->stageMap[$row1[2]]] += max($data2[$key][3] ?? 0, 0);
            $this->thirdPart[2][$this->stageMap[$row1[2]]] += (($data2[$key][3] ?? 0) - ($data1[$key][3] ?? 0)) < 0 ? abs(($data2[$key][3] ?? 0) - ($data1[$key][3] ?? 0)) : 0;

            if ($data2[$key][2] != 'Closed') {
                // Moved to Stage 1
                // From Stage 2&3 to Stage 1
                $this->thirdPart[3][0] += ($this->stageMap[$data1[$key][2]] != 0 and $this->stageMap[$data2[$key][2]] == 0) ? max($data2[$key][4], 0) : 0;
                // From Stage 2 to Stage 1
                $this->thirdPart[3][1] += ($this->stageMap[$data1[$key][2]] == 1 and $this->stageMap[$data2[$key][2]] == 0) ? max($data2[$key][4], 0) : 0;
                // From Stage 3 to Stage 1
                $this->thirdPart[3][2] += ($this->stageMap[$data1[$key][2]] == 2 and $this->stageMap[$data2[$key][2]] == 0) ? max($data2[$key][4], 0) : 0;

                // Moved to Stage 2
                // From Stage 1 to Stage 2
                $this->thirdPart[4][0] += ($this->stageMap[$data1[$key][2]] == 0 and $this->stageMap[$data2[$key][2]] == 1) ? max($data2[$key][4], 0) : 0;
                // From Stage 1&3 to Stage 2
                $this->thirdPart[4][1] += ($this->stageMap[$data1[$key][2]] != 1 and $this->stageMap[$data2[$key][2]] == 1) ? max($data2[$key][4], 0) : 0;
                // From Stage 3 to Stage 2
                $this->thirdPart[4][2] += ($this->stageMap[$data1[$key][2]] == 2 and $this->stageMap[$data2[$key][2]] == 1) ? max($data2[$key][4], 0) : 0;

                // Moved to Stage 3
                // From Stage 1 to Stage 3
                $this->thirdPart[5][0] += ($this->stageMap[$data1[$key][2]] == 0 and $this->stageMap[$data2[$key][2]] == 2) ? max($data2[$key][4], 0) : 0;
                // From Stage 2 to Stage 3
                $this->thirdPart[5][1] += ($this->stageMap[$data1[$key][2]] == 1 and $this->stageMap[$data2[$key][2]] == 2) ? max($data2[$key][4], 0) : 0;
                // From Stage 1&2 to Stage 3
                $this->thirdPart[5][2] += ($this->stageMap[$data1[$key][2]] != 2 and $this->stageMap[$data2[$key][2]] == 2) ? max($data2[$key][4], 0) : 0;
            }


        }

        $this->sheet->setCellValue('E1', $total[0]);
        $this->sheet->setCellValue('F1', $total[1]);
        $this->sheet->setCellValue('G1', $total[2]);
        $this->sheet->setCellValue('H1', $total[3]);

    }

    public function addSecondPartHeader()
    {
        $label = 'ﯾوﺿﺢ الجدول اﻟﺗﺎﻟﻲ التغيير ﻓﻲ أرﺻدة التسهيلات اﻻﺋﺗﻣﺎﻧﯾﺔ المباشرة الممنوحة للشركات الكبرى خلال السنة:';
        $this->sheet->mergeCells('J2:R2');
        $this->sheet->setCellValue('J2', $label);

        $this->sheet->mergeCells('J3:P3');
        $this->sheet->setCellValue('J3', $this->getDate('second'));

        $this->sheet->setCellValue('J4', 'المجموع');
        $this->sheet->setCellValue('P4', 'مرحلة 1');
        $this->sheet->setCellValue('N4', 'مرحلة 2');
        $this->sheet->setCellValue('L4', 'مرحلة 3');
        $this->sheet->setCellValue('J5', $this->currency);
        $this->sheet->setCellValue('N5', $this->currency);
        $this->sheet->setCellValue('L5', $this->currency);
        $this->sheet->setCellValue('P5', $this->currency);

        $this->sheet->setCellValue('R6', $this->getDate());
        $this->sheet->setCellValue('R7', 'التسهيلات الجديدة');
        $this->sheet->setCellValue('R8', 'التسهيلات المُسددة');
        $this->sheet->setCellValue('R9', 'المحول إلى المرحلة 1');
        $this->sheet->setCellValue('R10', 'المحول إلى المرحلة 2');
        $this->sheet->setCellValue('R11', 'المحول إلى المرحلة 3');
        $this->sheet->setCellValue('R12', 'ديون مشطوبة او محولة الى بنود خارج الميزانية');
        $this->sheet->setCellValue('R13', 'الرصيد النهائي');

    }

    public function addThirdPartHeader()
    {
        $label = 'فيما يلي تفاصيل حركة مخصص تدني الخسائر الائتمانية المتوقعة للتسهيلات الائتمانية المباشرة الممنوحة';
        $this->sheet->mergeCells('J16:R16');
        $this->sheet->setCellValue('J16', $label);

//        $this->sheet->mergeCells('J16:P16');
//        $this->sheet->setCellValue('J16', $this->getDate('second'));

        $this->sheet->setCellValue('N17', 'مرحلة 2');
        $this->sheet->setCellValue('L17', 'مرحلة 3');
        $this->sheet->setCellValue('P17', 'مرحلة 1');
        $this->sheet->setCellValue('J18', $this->currency);
        $this->sheet->setCellValue('N18', $this->currency);
        $this->sheet->setCellValue('L18', $this->currency);
        $this->sheet->setCellValue('P18', $this->currency);

        $this->sheet->setCellValue('R19', $this->getDate());
        $this->sheet->setCellValue('R20', 'المحول إلى المرحلة 1');
        $this->sheet->setCellValue('R21', 'المحول إلى المرحلة 2');
        $this->sheet->setCellValue('R22', 'المحول إلى المرحلة 3');
        $this->sheet->setCellValue('R23', 'المستخدم من المخصص(ديون مشطوبة او محولة الى بنود خارج الميزانية)');
        $this->sheet->setCellValue('R24', 'صافي الخسائر الائتمانية/استرداد الخسائر الائتمانية للسنة');
        $this->sheet->setCellValue('R25', 'الرصيد النهائي');

    }

    public function addSecondPartData()
    {
        $data = $this->secondPart;

        $this->sheet->setCellValue('P6', $data[0][0]);
        $data[7][0] += $data[0][0];
        $this->sheet->setCellValue('N6', $data[0][1]);
        $data[7][1] += $data[0][1];
        $this->sheet->setCellValue('L6', $data[0][2]);
        $data[7][2] += $data[0][2];
        $this->sheet->setCellValue('J6', $data[0][0] + $data[0][1] + $data[0][2]);
        $data[7][3] += $data[0][0] + $data[0][1] + $data[0][2];

        $this->sheet->setCellValue('P7', $data[1][0]);
        $data[7][0] += $data[1][0];
        $this->sheet->setCellValue('N7', $data[1][1]);
        $data[7][1] += $data[1][1];
        $this->sheet->setCellValue('L7', $data[1][2]);
        $data[7][2] += $data[1][2];
        $this->sheet->setCellValue('J7', $data[1][0] + $data[1][1] + $data[1][2]);
        $data[7][3] += $data[1][0] + $data[1][1] + $data[1][2];

        $this->sheet->setCellValue('P8', $data[2][0]);
        $data[7][0] += $data[2][0];
        $this->sheet->setCellValue('N8', $data[2][1]);
        $data[7][1] += $data[2][1];
        $this->sheet->setCellValue('L8', $data[2][2]);
        $data[7][2] += $data[2][2];
        $this->sheet->setCellValue('J8', $data[2][0] + $data[2][1] + $data[2][2]);
        $data[7][3] += $data[2][0] + $data[2][1] + $data[2][2];


        $this->sheet->setCellValue('P9', $data[3][0]);
        $data[7][0] += $data[3][0];
        $this->sheet->setCellValue('N9', $data[3][1]);
        $data[7][1] += $data[3][1];
        $this->sheet->setCellValue('L9', $data[3][2]);
        $data[7][2] += $data[3][2];
        $this->sheet->setCellValue('J9', $data[3][0] + $data[3][1] + $data[3][2]);
        $data[7][3] += $data[3][0] + $data[3][1] + $data[3][2];


        $this->sheet->setCellValue('P10', $data[4][0]);
        $data[7][0] += $data[4][0];
        $this->sheet->setCellValue('N10', $data[4][1]);
        $data[7][1] += $data[4][1];
        $this->sheet->setCellValue('L10', $data[4][2]);
        $data[7][2] += $data[4][2];
        $this->sheet->setCellValue('J10', $data[4][0] + $data[4][1] + $data[4][2]);
        $data[7][3] += $data[4][0] + $data[4][1] + $data[4][2];

        $this->sheet->setCellValue('P11', $data[5][0]);
        $data[7][0] += $data[5][0];
        $this->sheet->setCellValue('N11', $data[5][1]);
        $data[7][1] += $data[5][1];
        $this->sheet->setCellValue('L11', $data[5][2]);
        $data[7][2] += $data[5][2];
        $this->sheet->setCellValue('J11', $data[5][0] + $data[5][1] + $data[5][2]);
        $data[7][3] += $data[5][0] + $data[5][1] + $data[5][2];

        $this->sheet->setCellValue('P12', $data[6][0]);
        $data[7][0] += $data[6][0];
        $this->sheet->setCellValue('N12', $data[6][1]);
        $data[7][2] += $data[6][2];
        $this->sheet->setCellValue('L12', $data[6][2]);
        $data[7][2] += $data[6][2];
        $this->sheet->setCellValue('J12', $data[6][0] + $data[6][1] + $data[6][2]);
        $data[7][3] += $data[6][0] + $data[6][1] + $data[6][2];


        $this->sheet->setCellValue('P13', $data[7][0]);
        $this->sheet->setCellValue('N13', $data[7][1]);
        $this->sheet->setCellValue('L13', $data[7][2]);
        $this->sheet->setCellValue('J13', $data[7][3]);


    }

    public function addThirdPartData()
    {
        $data = $this->thirdPart;

        $this->sheet->setCellValue('P19', $data[0][0]);
        $data[7][0] += $data[0][0];
        $this->sheet->setCellValue('N19', $data[0][1]);
        $data[7][1] += $data[0][1];
        $this->sheet->setCellValue('L19', $data[0][2]);
        $data[7][2] += $data[0][2];
        $this->sheet->setCellValue('J19', $data[0][0] + $data[0][1] + $data[0][2]);
        $data[7][3] += $data[0][0] + $data[0][1] + $data[0][2];

        $this->sheet->setCellValue('P20', $data[1][0]);
        $data[7][0] += $data[1][0];
        $this->sheet->setCellValue('N20', $data[1][1]);
        $data[7][1] += $data[1][1];
        $this->sheet->setCellValue('L20', $data[1][2]);
        $data[7][2] += $data[1][2];
        $this->sheet->setCellValue('J20', $data[1][0] + $data[1][1] + $data[1][2]);
        $data[7][3] += $data[1][0] + $data[1][1] + $data[1][2];

        $this->sheet->setCellValue('P21', $data[2][0]);
        $data[7][0] += $data[2][0];
        $this->sheet->setCellValue('N21', $data[2][1]);
        $data[7][1] += $data[2][1];
        $this->sheet->setCellValue('L21', $data[2][2]);
        $data[7][2] += $data[2][2];
        $this->sheet->setCellValue('J21', $data[2][0] + $data[2][1] + $data[2][2]);
        $data[7][3] += $data[2][0] + $data[2][1] + $data[2][2];


        $this->sheet->setCellValue('P22', $data[3][0]);
        $data[7][0] += $data[3][0];
        $this->sheet->setCellValue('N22', $data[3][1]);
        $data[7][1] += $data[3][1];
        $this->sheet->setCellValue('L22', $data[3][2]);
        $data[7][2] += $data[3][2];
        $this->sheet->setCellValue('J22', $data[3][0] + $data[3][1] + $data[3][2]);
        $data[7][3] += $data[3][0] + $data[3][1] + $data[3][2];


        $this->sheet->setCellValue('P23', $data[4][0]);
        $data[7][0] += $data[4][0];
        $this->sheet->setCellValue('N23', $data[4][1]);
        $data[7][1] += $data[4][1];
        $this->sheet->setCellValue('L23', $data[4][2]);
        $data[7][2] += $data[4][2];
        $this->sheet->setCellValue('J23', $data[4][0] + $data[4][1] + $data[4][2]);
        $data[7][3] += $data[4][0] + $data[4][1] + $data[4][2];

        $this->sheet->setCellValue('P24', $data[5][0]);
        $data[7][0] += $data[5][0];
        $this->sheet->setCellValue('N24', $data[5][1]);
        $data[7][1] += $data[5][1];
        $this->sheet->setCellValue('L24', $data[5][2]);
        $data[7][2] += $data[5][2];
        $this->sheet->setCellValue('J24', $data[5][0] + $data[5][1] + $data[5][2]);
        $data[7][3] += $data[5][0] + $data[5][1] + $data[5][2];


        $this->sheet->setCellValue('P25', $data[7][0]);
        $this->sheet->setCellValue('N25', $data[7][1]);
        $this->sheet->setCellValue('L25', $data[7][2]);
        $this->sheet->setCellValue('J25', $data[7][3]);


    }

    private function setStyle()
    {
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $cellRange = 'A1:ZZ100'; // All headers
        $this->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);

        $headersRange = 'A2:ZZ3'; // All headers
        $this->sheet->getDelegate()->getStyle($headersRange)->getFont()->setBold(true);
        $headersRange = 'R4:R30'; // All headers
        $this->sheet->getDelegate()->getStyle($headersRange)->getFont()->setBold(true);

        $headersRange = 'J16:R16'; // All headers
        $this->sheet->getDelegate()->getStyle($headersRange)->getFont()->setBold(true);

    }


}
