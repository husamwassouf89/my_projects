<?php

namespace App\Http\Controllers;

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

    public function import(ImportRequest $request)
    {
        if ($this->service->import()) {
            return $this->response('success');
        }
        return $this->response('failed');

    }
}
