<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staging\StagingQuestionRequest;
use App\Http\Requests\Staging\StagingRequest;
use App\Http\Requests\Staging\UpdateStagingQuestionRequest;
use App\Services\StagingService;
use Illuminate\Http\JsonResponse;

class StagingController extends Controller
{
    public function __construct(StagingService $service)
    {
        $this->service = $service;
    }

    public function index($id): JsonResponse
    {
        return $this->response('success', $this->service->index($id));
    }

    public function store(StagingQuestionRequest $request): JsonResponse
    {
        return $this->response('success', $this->service->store($request->validated()));
    }

    public function update($id, UpdateStagingQuestionRequest $request): JsonResponse
    {
        return $this->response('success', $this->service->update($id, $request->validated()));
    }

    public function show($id): JsonResponse
    {
        return $this->response('success', $this->service->show($id));
    }

    public function destroy($id): JsonResponse
    {
        return $this->response('success', $this->service->delete($id));
    }
}