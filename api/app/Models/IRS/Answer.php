<?php

namespace App\Models\IRS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeJoins($query)
    {
        $query->join('options', 'options.id', '=', 'answers.option_id')
            ->join('questions', 'questions.id', '=', 'options.question_id');
    }

    public function scopeSelectShow($query)
    {
        $query->joins()->select('answers.*', 'questions.text as question_text','options.text as answer_text',
            'options.value as answer_value', 'options.question_id');
    }

}
