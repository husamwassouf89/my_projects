<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\User\UpdateSessionUserRequest;
use App\Http\Requests\User\DeleteUserIdsRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UserIdRequest;
use App\Http\Requests\User\UserRequest;
use App\Services\UserService;

class UserController extends Controller
{

    public function __construct(UserService $service)
    {
        $this->service = $service;

        $this->middleware('has.permissions:View Users')->only(['index']);
        $this->middleware('has.permissions:Add User')->only(['store']);
        $this->middleware('has.permissions:Edit User')->only(['edit', 'update']);
        $this->middleware('has.permissions:Show User')->only(['show']);
        $this->middleware('has.permissions:Delete User')->only(['delete']);
    }

    public function index(PaginateRequest $request)
    {

        $users = $this->service->index($request->page_size, $request->keyword);
        return $this->response('success', $users, 200);

    }

    public function store(UserRequest $request)
    {

        $user = $this->service->store($request->email, $request->password, $request->employee_id, $request->role_id);
        if (!is_object($user) and $user == -1) {
            return $this->response('each_employee_can_have_only_one_user', 500);
        }
        if (!$user) return $this->response('failed', 500);

        return $this->response('success', $user, 200);
    }


    public function update(UpdateUserRequest $request)
    {
        $user = $this->service->update($request->id, $request->email, $request->password, $request->employee_id, $request->role_id);
        if ($user == -1) {
            return $this->response('each_employee_can_have_only_one_user', 500);
        }
        if (!$user) return $this->response('failed', 500);

        return $this->response('success', $user, 200);

    }

    public function delete(DeleteUserIdsRequest $request)
    {
        $isDeleted = $this->service->delete($request->ids);
        if (!$isDeleted) return $this->response('failed', 500);
        return $this->response('success', null, 200);

    }

    public function fetchUserInfo()
    {
        $data = $this->service->fetchUserInfo();

        return $this->response('success', $data);

    }

    public function updateUserInfo(UpdateSessionUserRequest $request)
    {
        $isUpdated = $this->service->updateUserInfo($request->name, $request->password, $request->mobile, $request->email);

        if (!$isUpdated) return $this->response('failed', 500);

        return $this->response('success');
    }

}
