<?php

namespace App\Http\Controllers;

use App\Http\Requests\IRS\IRSRequest;
use App\Http\Requests\IRS\ShowIRSRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\IRSService;

class IRSController extends Controller
{
    public function __construct(IRSService $service)
    {
        $this->service = $service;
    }

    public function index(PaginateRequest $request)
    {
        return $this->response('success', $this->service->index($request->validated()));
    }

    public function store(IRSRequest $request)
    {
        return $this->response('success', $this->service->store($request->validated()));
    }

    public function show(ShowIRSRequest $request)
    {
        return $this->response('success', $this->service->show($request->validated()));
    }

    public function destroy($id)
    {
        return $this->response('success', $this->service->destroy($id));
    }

    public function classTypePercentage()
    {
        return $this->response('success', $this->service->classTypePercentage());
    }


}
