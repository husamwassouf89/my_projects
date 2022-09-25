<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\ClassType\IdRequest as ClassTypeIdRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PD\IdRequest;
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

            if ($data == -1) {
                return $this->response('failed, you have already added a pd for the wanted specification', null);
            } else if($data == -2){
                return $this->response('failed, not suitable template has been used!', null,422);
            }
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }

    public function show($id, IdRequest $request)
    {
        return $this->response('success', $this->service->show($id));
    }

    public function showRaw($id)
    {
        return $this->response('success', $this->service->showRaw($id));
    }

    public function classTypeYears(ClassTypeIdRequest $request)
    {
        if ($data = $this->service->classTypeYears($request->id)) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }

    public function insertedYears(): \Illuminate\Http\JsonResponse
    {
        return $this->response('success', $this->service->insertedYears());
    }

    public function destroy($id, IdRequest $request)
    {
        if ($data = $this->service->destory($request->id)) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }
}
