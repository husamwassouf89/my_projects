<?php

namespace App\Http\Controllers;

use App\Http\Requests\PD\IdRequest;
use App\Http\Requests\Client\ClassType\IdRequest as ClassTypeIdRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PD\ImportRequest;
use App\Services\PDService;

class PDController extends Controller
{
    public function __construct(PDService $service)
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
        if ($data = $this->service->store($request->validated())) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }

    public function show($id, IdRequest $request)
    {
        return $this->response('success', $this->service->show($request->id));
    }

    public function classTypeYears(ClassTypeIdRequest $request)
    {
        if ($data = $this->service->classTypeYears($request->id)) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }

    public function destroy($id, IdRequest $request)
    {
        if ($data = $this->service->destory($request->id)) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }
}
