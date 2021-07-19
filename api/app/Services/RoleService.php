<?php


namespace App\Services;


use App\Http\Requests\Role\RoleIdRequest;
use App\Models\Permission\Permission;
use App\Models\Permission\PermissionRole;
use App\Models\Permission\Role;

class RoleService extends Service
{

    public function index(int $pageSize, ?string $keyword)
    {
        $roles = Role::with(['permissions'])->filter($keyword)->paginate($pageSize);
        return $this->handlePaginate($roles, 'roles');
    }

    public function store(string $name, array $permissions)
    {

        $role       = new Role();
        $role->name = $name;
        if(!$role->save()) null;

        foreach($permissions as $permission) {
            $permissionRole                = new PermissionRole();
            $permissionRole->role_id       = $role->id;
            $permissionRole->permission_id = $permission;
            $permissionRole->save();
        }

        $role->load('permissions');

        return $role;
    }

    public function show(int $id)
    {
         return Role::where('id', $id)->with('permissions')->first();

    }


    public function update(int $id, string $name, array $permissions)
    {

        $role = Role::where('id', $id)->first();
        if($name) $role->name = $name;
        $role->save();

        $role->permissions()->detach();

        if($permissions) {
            foreach($permissions as $permission) {
                $permissionRole                = new PermissionRole();
                $permissionRole->role_id       = $role->id;
                $permissionRole->permission_id = $permission;
                $permissionRole->save();
            }
        }


        $role->load('permissions');
        return $role;

    }

    public function delete(array $ids)
    {
        $roles = Role::whereIn('id', $ids)->get();
        $data  = [];
        foreach($roles as $role) {
            if(count($role->users) > 0) {
                array_push($data, ['id' => $role->id, 'name' => $role->name]);
            } else {
                $role->delete();
            }

        }
        if($data and count($data) > 0) {
            return null;
        }

        return true;
    }

    public function permissions()
    {
        return $permissions = Permission::orderBy('name', 'asc')->get();
    }

}
