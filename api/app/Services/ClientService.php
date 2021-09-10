<?php


namespace App\Services;


use App\Imports\ClientImport;
use App\Models\Client\Client;
use Maatwebsite\Excel\Facades\Excel;

class ClientService extends Service
{

    public function index($input)
    {
        $data = Client::allJoins()->selectIndex()->paginate($input['page_size']);
        return $this->handlePaginate($data, 'clients');

    }

    public function store($input)
    {
        Excel::import(new ClientImport(), $input['path']);
        return true;
    }

    public function show($id)
    {
        return Client::where('clients.id', $id)->joins()->selectShow()->first();
    }

    public function showByCif($cif)
    {
        return Client::where('clients.cif', $cif)->joins()->selectShow()->first();
    }


    private function calculateEAD($account)
    {
        $outstanding = $account->outstanding_lcy ?: 0;
        $accruedInterest = $account->accrued_interest_lcy ?: 0;
        $accruedInterest = $account->accrued_interest_lcy ?: 0;


    }

}
