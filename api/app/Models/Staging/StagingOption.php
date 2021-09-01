<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingOption extends Model
{
    use HasFactory;

    public static $TYPES = ['Yes', 'No', 'Others'];
    public $timestamps = false;
    public $guarded = ['id'];

    public static function getTypes(): string
    {
        $string = '';
        foreach (StagingOption::$TYPES as $item) {
            if ($string != '') $string .= ',';
            $string .= $item;
        };

        return $string;
    }

}
