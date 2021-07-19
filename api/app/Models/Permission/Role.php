<?php

namespace App\Models\Permission;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $id)
 * @method static whereIn(string $string, $ids)
 * @method static create(string[] $array)
 */
class Role extends Model
{
    use HasFactory;

    public    $timestamps = false;
    protected $guarded    = ['id'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles',
                                    'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeId($query, $id)
    {
        return $query->where('roles.id', $id);
    }

    public function scopeNotId($query, $id)
    {
        return $query->where('roles.id', '!=', $id);
    }

    public function scopeFilter($query, $name)
    {
        if($name) {
            $query->where('roles.name','like','%'.$name.'%');
        }
        return $query;

    }
}
