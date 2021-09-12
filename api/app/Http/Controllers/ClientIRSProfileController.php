<?php

namespace App\Http\Controllers;

use App\Http\Requests\IRS\ClientIRSProfileRequest;
use App\Services\ClientIRSProfileService;
use Illuminate\Http\JsonResponse;

class ClientIRSProfileController extends Controller
{
    public function __construct(ClientIRSProfileService $service)
    {
        $this->service = $service;
    }

    public function index($id)
    {
        return $this->response('success', $this->service->index($id));
    }

    public function show($id): JsonResponse
    {
        return $this->response('success', $this->service->show($id));
    }

    public function store(ClientIRSProfileRequest $request): JsonResponse
    {
        return $this->response('success', $this->service->store($request->validated()));
    }

    public function destroy($id): JsonResponse
    {
        return $this->response('success', $this->service->destroy($id));
    }

}
