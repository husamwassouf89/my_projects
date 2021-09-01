<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingAnswer extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function scopeJoins($query)
    {
        $query->join('staging_options', 'staging_options.id', '=', 'staging_answers.option_id')
              ->join('staging_questions', 'staging_questions.id', '=', 'staging_options.question_id');
    }

    public function scopeSelectShow($query)
    {
        $query->joins()->select('staging_answers.*', 'staging_questions.text as question_text',
                                'staging_options.text as answer_text', 'staging_options.type as answer_type',
                                'staging_options.question_id');
    }
}
