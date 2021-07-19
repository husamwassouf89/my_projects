<?php


namespace App\Services;


use App\Imports\ClientImport;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{
    public function import()
    {
        Excel::import(new ClientImport(), request()->file('file'));

        return true;
    }

}
