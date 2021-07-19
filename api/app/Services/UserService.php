<?php


namespace App\Services;


use App\Models\Management\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserService extends Service
{


    public function index(int $pageSize, ?string $keyword)
    {
        $this->data = User::filter($keyword)->selectIndex()->paginate($pageSize);
        return $this->handlePaginate($this->data, 'users');
    }

    public function store(string $email, string $password, int $employeeId, int $roleId)
    {
        if (User::where('employee_id', $employeeId)->first()) {
            return -1;
        }
        $user              = new User();
        $user->email       = $email;
        $user->password    = $password;
        $user->employee_id = $employeeId;
        $user->role_id     = $roleId;

        if (!$user->save()) return null;

        return $this->show($user->id);
    }

    private function show(int $id)
    {
        return User::id($id)->selectIndex()->first();
    }


    public function update(int $id, string $email, ?string $password, int $employeeId, int $roleId)
    {
        $user = User::id($id)->first();
        if (User::where('employee_id', $employeeId)->where('id', '!=', $id)->first()) {
            return -1;
        }
        $user->email = $email;
        if ($password) $user->password = $password;
        $user->employee_id = $employeeId;
        $user->role_id     = $roleId;

        if (!$user->save()) null;

        return $this->show($user->id);

    }

    public function delete(array $ids)
    {
        $users = User::whereIn('id', $ids)->get();

        $data = [];
        foreach ($users as $user) {
            if ($user->id == 1) {
                array_push($data, ['id' => $user->id, 'name' => $user->name]);
            } else {
                $user->delete();

            }

        }
        if ($data and count($data) > 0) {
            return null;
        }

        return true;

    }

    public function fetchUserInfo()
    {
        $user = Auth::user();
        $data = [
            'name'   => $user->employee->name,
            'email'  => $user->email,
            'mobile' => $user->employee->mobile ?? null,
        ];

        return $data;

    }

    public function updateUserInfo(?string $name, ?string $password, ?string $mobile, ?string $email)
    {
        $user = Auth::user();
        if ($email) {
            $user->email = $email;

        }
        if ($name) {
            $user->employee()->update(['name' => $name]);
        }
        if ($mobile) {
            $user->employee()->update(['mobile' => $mobile]);
        }
        if ($password) {
            $user->password = $password;
        }

        $user->save();

        return true;
    }


}
