<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\ClientIdRequest;
use App\Http\Requests\Client\ImportRequest;
use App\Services\AuthService;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function __construct(ClientService $service)
    {
        $this->service = $service;
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

        return $this->response($this->service->show($request->id));

    }
}
