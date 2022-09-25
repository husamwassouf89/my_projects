<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachmentRequest;
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

    public function indexIRS(PaginateRequest $request)
    {
        if ($data = $this->service->indexIRS($request->validated())) {
            return $this->response('success', $data);
        }
        return $this->response('failed');
    }

    public function indexStage(PaginateRequest $request)
    {
        if ($data = $this->service->indexStage($request->validated())) {
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
        return $this->response('success', $this->service->show($id, request()->balance, null, null, request()->limit));
    }

    public function showByCif($cif)
    {
        return $this->response('success', $this->service->showByCif($cif, request()->balance, request()->limit,request()->year,request()->quarter));
    }

    public function changeFinancialStatus(FinancialData $request)
    {
        return $this->response('success', $this->service->changeFinancialStatus($request->validated()));
    }


    public function setGrade($id, $gradeId)
    {
        return $this->response('success', $this->service->setGrade($id, $gradeId));
    }

    public function setStage($id, $stageId)
    {
        return $this->response('success', $this->service->setStage($id, $stageId));
    }

    public function getClassTypeGrades($id)
    {
        return $this->response('success', $this->service->getClassTypeGrades($id));
    }

    public function addAttachments($id, AttachmentRequest $request)
    {
        return $this->response('success', $this->service->addAttachments($id, $request->attachment_ids));
    }


}
