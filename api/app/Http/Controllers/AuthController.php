<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{

    public function __construct(AuthService $service)
    {
        $this->service = $service;
        $this->middleware('auth:api')->only('logout');
    }

    public function login(LoginRequest $request)
    {
        $this->service->login($request->email, $request->password);

        if($this->service->isLogged) {
            return $this->response('success', $this->service->loginData, 200);
        }

        return $this->response('failed', null, 422);


    }

    public function logout(): JsonResponse
    {
        if($this->service->logout()) {
            return $this->response('success', $this->service->loginData, 200);
        } else {
            return $this->response('failed', null, 422);
        }

    }



}
