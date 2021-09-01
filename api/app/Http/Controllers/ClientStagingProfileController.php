<?php

namespace App\Http\Controllers;

use App\Http\Requests\IRS\ClientIRSProfileRequest;
use App\Http\Requests\Staging\ClientStagingProfileRequest;
use App\Services\ClientIRSProfileService;
use App\Services\ClientStagingProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientStagingProfileController extends Controller
{
    public function __construct(ClientStagingProfileService $service)
    {
        $this->service = $service;
    }

    public function show($id): JsonResponse
    {
        return $this->response('success', $this->service->show($id));
    }

    public function store(ClientStagingProfileRequest $request): JsonResponse
    {
        return $this->response('success', $this->service->store($request->validated()));
    }

    public function destroy($id): JsonResponse
    {
        return $this->response('success', $this->service->destroy($id));
    }

}
