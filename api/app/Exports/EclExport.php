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

class EclExport implements WithEvents, WithHeadings, FromArray
{
    use HelpKit;

    private $q, $year, $classType, $date1, $date2;

    public function __construct($q, $year, $type, $limits, $category)
    {
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
        return ['Portfolio', 'CIF', 'Branch', 'Name', 'Stage', 'ECL ON', 'ECL OFF', 'ECL'];
    }

    public function array(): array
    {
        return $this->getClientsData();
    }

    public function getClientsData()
    {
        $ids           = Client::select('id')->get()->pluck('id')->toArray();
        $clients       = [];
        $clientService = new ClientService();
        foreach ($ids as $id) {
            $client = $clientService->show($id);
            $temp   = [];
            $ok     = false;
            if (count($client->clientAccounts) == 0) {
                $client->ecl = 0;
            }
            foreach ($client->clientAccounts as $account) {
                foreach ($account->accountInfos as $info) {

                    $firstDate  = $this->getDateRange($info->year, $info->quarter)['first_date'];
                    $secondDate = $this->getDateRange($info->year, $info->quarter)['last_date'];
                    $firstDate  = Carbon::createFromTimeString($firstDate);
                    $secondDate = Carbon::createFromTimeString($secondDate);
                    if (!($firstDate <= $this->date1 and $secondDate <= $this->date2)) {
                        continue;
                    }


                    if (!$info->final_grade or !is_numeric($info->pd) or !$info->stage) continue;
                    if (!$ok) {
                        array_push($temp, $client->class_type_name);
                        array_push($temp, $client->cif);
                        array_push($temp, $client->branch ?? "NONE");
                        array_push($temp, $client->name);
                        array_push($temp, $info->stage);
                    }
                    $ok          = true;
                    $client->ecl += $info->ecl;
                    break;
                }
            }
            $onEcl = $client->ecl ?? 0;
            array_push($temp, $onEcl);


            $client = $clientService->show($id, 'off');
            foreach ($client->clientAccounts as $account) {
                foreach ($account->accountInfos as $info) {
                    if (!$info->final_grade or !is_numeric($info->pd) or !$info->stage) continue;
                    $client->off_ecl += 1;
                    $ok              = true;
                    break;
                }
            }
            $offEcl = $client->off_ecl ?? 0;
            array_push($temp, (string)$offEcl);
            array_push($temp, $onEcl + $offEcl);

            if ($ok)
                if (count($temp) > 5)
                    array_push($clients, $temp);
        }

        return $clients;
    }
}
