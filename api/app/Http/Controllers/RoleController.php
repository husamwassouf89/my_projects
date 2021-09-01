<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\DeleteRoleIdsRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Role\RoleIdRequest;
use App\Http\Requests\Role\RoleRequest;
use App\Models\Permission\Role;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(RoleService $service)
    {
        $this->service = $service;

        $this->middleware('has.permissions:View Roles')->only(['index']);
        $this->middleware('has.permissions:Add Role')->only(['store']);
        $this->middleware('has.permissions:Edit Role')->only(['edit', 'update']);
        $this->middleware('has.permissions:Show Role')->only(['show']);
        $this->middleware('has.permissions:Delete Role')->only(['delete']);
    }

    public function index(PaginateRequest $request)
    {
        $roles = $this->service->index($request->page_size, $request->keyword);
        if($roles) {
            return $this->response('success', $roles, 200);
        } else {
            return $this->response('failed', null, 404);
        }

    }

    public function store(RoleRequest $request)
    {
       $role = $this->service->store($request->name, $request->permissions);
        return $this->response('success', $role, 200);
    }

    public function show(RoleIdRequest $request)
    {
        $user = $this->service->show($request->id);
        return $this->response('success', $user, 200);
    }

    public function update(RoleIdRequest $request)
    {
        $role = $this->service->update($request->id, $request->name,$request->permissions);
        if (!$role) return $this->response('failed', 500);
        return $this->response('success', $role, 200);
    }

    public function delete(DeleteRoleIdsRequest $request)
    {
        $roles = Role::whereIn('id', $request->ids)->get();
        $data  = [];
        foreach($roles as $role) {
            if(count($role->users) > 0) {
                array_push($data, ['id' => $role->id, 'name' => $role->name]);
            } else {
                $role->delete();
            }

        }
        if($data and count($data) > 0) {
            return $this->response('these_roles_can\'t_be_deleted', $data, 200);
        }

        return $this->response('success', null, 200);
    }

    public function permissions(PaginateRequest $request)
    {
        return $this->response('success', $this->service->permissions(), 200);
    }
}
