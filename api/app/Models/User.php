<?php

namespace App\Models;

use App\Models\Permission\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guard
        = [
            'id',
        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden
        = [
            'password',
            'updated_at',
            'created_at',
        ];


    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = Str::lower($email);
    }

    public function scopeSelectIndex($query)
    {
        return $query->joins()->select('users.id', 'users.name', 'users.email', 'roles.name as role',
                                       'users.role_id', 'users.employee_id')->with(['role']);
    }


    public function scopeJoins($query)
    {
        return $query->join('roles', 'roles.id', '=', 'users.role_id');
    }

    public function scopeId($query, $id)
    {
        return $query->where('users.id', $id);
    }

    public function scopeNotId($query, $id)
    {
        return $query->where('users.id', '!=', $id);
    }

    public function scopeFilter($query, ?string $keyword)
    {
        $tables['users'] = Schema::getColumnListing('users');
        $tables['roles'] = Schema::getColumnListing('roles');

        if ($keyword) {
            foreach ($tables as $key => $table) {
                foreach ($table as $column)
                    $query->orWhere($key . '.' . $column, 'LIKE', '%' . $keyword . '%');
            }
        }

        return $query;
    }

    public function hasOnePermissionAtLeast(array $permissions): bool
    {
        $userPermissions = $this->role->permissions;
        foreach ($permissions as $item) {
            foreach ($userPermissions as $item2) {
                if (strtolower($item2->name) == strtolower($item)) {
                    return true;
                }
            }
        }
        return false;


    }
}
