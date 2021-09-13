<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingAnswer extends Model
{
    use HasFactory;


    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['updated_at'];

    public function scopeJoins($query)
    {
        $query->join('staging_options', 'staging_options.id', '=', 'staging_answers.staging_option_id')
              ->join('staging_questions', 'staging_questions.id', '=', 'staging_options.staging_question_id');
    }

    public function scopeSelectShow($query)
    {
        $query->joins()->select('staging_answers.*', 'staging_questions.text as question_text',
                                'staging_options.text as answer_text', 'staging_options.type as answer_type',
                                'staging_options.staging_question_id', 'staging_options.with_value');
    }

    public function option()
    {
        return $this->belongsTo(StagingOption::class);
    }
}
