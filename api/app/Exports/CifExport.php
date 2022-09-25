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

class CifExport implements WithEvents, WithHeadings, FromArray
{

    use HelpKit;

    private $q1, $q2, $year1, $year2, $category, $date1, $date2;

    public function __construct($q, $year, $type, $limits, $category)
    {
        $this->q        = $q;
        $this->year     = $year;
        $this->type     = $type;
        $this->limits   = $limits;
        $this->category = $category;
        $temp           = $this->getDateRange($this->year, $this->q);
        $this->date1    = $temp['first_date'];
        $this->date2    = $temp['last_date'];

        $this->date1 = Carbon::createFromTimeString($this->date1);
        $this->date2 = Carbon::createFromTimeString( $this->date2);

        $this->guarantee_type = ['cm_guarantee', 'pv_securities_guarantees', 'pv_re_guarantees'];
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
        return ['Portfolio', 'CIF', 'Name', 'Final Grade', 'Stage', 'PD', 'EAD', 'LGD', 'ECL'];
    }

    public function array(): array
    {
        return $this->getClientsData();
    }

    private function getClientsData()
    {
        $ids = Client::join('class_types', 'class_types.id', '=', 'clients.class_type_id')
                     ->where('class_types.category', $this->category)
                     ->select('clients.id')->get()->pluck('id')->toArray();

        $clients       = [];
        $clientService = new ClientService();
        foreach ($ids as $id) {
            $client = $clientService->show($id);
            foreach ($client->clientAccounts as $account) {
                foreach ($account->accountInfos as $info) {

                    $firstDate  = $this->getDateRange($info->year, $info->quarter)['first_date'];
                    $secondDate = $this->getDateRange($info->year, $info->quarter)['last_date'];
                    $firstDate = Carbon::createFromTimeString($firstDate);
                    $secondDate = Carbon::createFromTimeString($secondDate);
                    if (!($firstDate<= $this->date1 and $secondDate <= $this->date2)) {
                        continue;
                    }

                    if (!$info->final_grade or !is_numeric($info->pd) or !$info->stage) continue;
                    array_push($clients, [$client->class_type_name, $client->cif, $client->name, $info->final_grade, $info->stage, $info->pd, $info->ead, $info->lgd, $info->ecl]);
                    break;
                }
            }
        }

        return $clients;
    }

}
