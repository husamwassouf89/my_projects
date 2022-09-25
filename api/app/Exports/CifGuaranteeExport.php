<?php

namespace App\Exports;

use App\Models\Client\Client;
use App\Services\ClientService;
use App\Traits\HelpKit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class CifGuaranteeExport implements WithEvents, WithHeadings, FromArray
{

    use HelpKit;

    private $guaranteeTypes = [];
    private $q1, $q2, $year1, $year2, $category, $date1, $date2;

    public function __construct($q, $year, $type, $limits, $category)
    {
        $this->guaranteeTypes = ['cm_guarantee', 'pv_securities_guarantees', 'pv_re_guarantees'];

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

    public function registerEvents(): array
    {

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $cellRange = 'A1:ZZ100'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);

                $headersRange = 'A1:Z1'; // All headers
                $event->sheet->getDelegate()->getStyle($headersRange)->getFont()->setBold(true);
            },
        ];
    }

    public function headings(): array
    {
        $headers = ['Portfolio', 'CIF', 'Name'];
        foreach ($this->guaranteeTypes as $item) {
            array_push($headers, $item);
        }
        array_push($headers, 'Total Guarantee');
        return $headers;
    }

    public function array(): array
    {
        return $this->getClientsData();
    }

    public function getClientsData()
    {
        $ids           = Client::join('class_types', 'class_types.id', '=', 'clients.class_type_id')
                               ->where('class_types.category', $this->category)
                               ->select('clients.id')->get()->pluck('id')->toArray();
        $clients       = [];
        $clientService = new ClientService();
        $temp= [];
        foreach ($ids as $id) {
            $ok      = false;
            $client  = $clientService->show($id);
            $temp[0] = $client->class_type_name;
            $temp[1] = $client->cif;
            $temp[2] = $client->name;
            $temp[3] = 0;
            $temp[4] = 0;
            $temp[5] = 0;
//            if($client->cif != '25842')continue;
            foreach ($client->clientAccounts as $key => $account) {
                foreach ($account->accountInfos as $info) {
                    $firstDate  = $this->getDateRange($info->year, $info->quarter)['first_date'];
                    $secondDate = $this->getDateRange($info->year, $info->quarter)['last_date'];
                    $firstDate  = Carbon::createFromTimeString($firstDate);
                    $secondDate = Carbon::createFromTimeString($secondDate);
                    if (!($firstDate <= $this->date1 and $secondDate <= $this->date2)) {
                        continue;
                    }
                    if (!$info->final_grade or !is_numeric($info->pd) or !$info->stage) continue;
                    $ok      = true;
//                    dd($info);
                    $temp[3] += $info->cm_guarantee;
                    $temp[4] += $info->pv_securities_guarantees;
                    $temp[5] += $info->pv_re_guarantees;
//                    foreach ($this->guaranteeTypes as $key => $item) {
//                        $temp[$key + 3] += $info->id;
//                    }
                    break;
                }
            }
            // Total Guarantee
            $temp[6] = $temp[3]+$temp[4]+$temp[5];
            // Zero-Integer values considered as empty in excel
            foreach ($temp as $key => $value) {
                $temp[$key] = (string)$value;
            }
            if ($ok){
                array_push($clients, $temp);
            }

        }

        return $clients;
    }

}
