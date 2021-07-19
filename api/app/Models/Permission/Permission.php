<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static orderBy(string $string, string $string1)
 * @method static create(string[] $array)
 */
class Permission extends Model
{
    use HasFactory;

    public $timestamps = false;

}
