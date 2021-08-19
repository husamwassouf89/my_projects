<?php


namespace App\Services;


use App\Imports\ClientImport;
use App\Models\Client\Client;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{
    public function store($input)
    {
        Excel::import(new ClientImport(), $input['path']);

        return true;
    }

    public function show($id)
    {
        return Client::where('clients.id', $id)->joins()->selectShow()->first();
    }

}
