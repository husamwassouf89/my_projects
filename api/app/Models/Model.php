<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @method static find($id)
 * @method static leftJoin(string $string, string $string1, string $string2, string $string3)
 * @method static selectForEdit($id)
 * @method static id($id)
 * @method static userJoins()
 * @method static whereEmail($email)
 * @method static create(array $array)
 * @method static joins()
 */
abstract class Model extends EloquentModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guard = [
        'id',
    ];

    public $timestamps = false;

    abstract public function scopeSelectIndex(Builder $query);

    abstract public function scopeSelectShow(Builder $query);

    abstract public function scopeJoins(Builder $query);

    public function scopeId(Builder $query, $id)
    {
        return $query->where($query->getModel()->table . '.id', $id);
    }

    public function scopeNotId($query, $id)
    {
        return $query->where($this->table . '.id', '!=', $id);
    }

    public function scopeFilter(Builder $query, ?string $keyword)
    {
        $tables[$query->getModel()->getTable()] = Schema::getColumnListing($query->getModel()->getTable());

        if ($keyword) {
            foreach ($tables as $key => $table) {
                foreach ($table as $column)
                    $query->orWhere($key . '.' . $column, 'LIKE', '%' . $keyword . '%');
            }
        }

        return $query;
    }
}
