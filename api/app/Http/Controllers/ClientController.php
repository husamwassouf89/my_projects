<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\ClientIdRequest;
use App\Http\Requests\Client\FinancialData;
use App\Http\Requests\Client\ImportRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\ClientService;

class ClientController extends Controller
{

    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function index(PaginateRequest $request)
    {
        if ($data = $this->service->index($request->validated())) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }

    public function store(ImportRequest $request)
    {
        if ($this->service->store($request->validated())) {
            return $this->response('success');
        }
        return $this->response('failed');
    }

    public function show($id, ClientIdRequest $request)
    {
        return $this->response('success', $this->service->show($id));
    }

    public function showByCif($cif)
    {
        return $this->response('success', $this->service->showByCif($cif));
    }

    public function changeFinancialDataStatus(FinancialData $request)
    {
        return $this->response('success', $this->service->changeFinancialDataStatus($request->validated()));
    }
}
