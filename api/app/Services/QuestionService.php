<?php


namespace App\Services;


use App\Models\IRS\Question;

class QuestionService extends Service
{
    public function store($input)
    {
        $question = Question::create([
                                         'irs_id'            => $input['irs_id'],
                                         'text'              => $input['text'],
                                         'max_options_value' => $input['max_options_value'],
                                     ]);

        foreach ($input['options'] as $option) {
            $question->options()->create([
                                             'text'  => $option['text'],
                                             'value' => $option['value'],
                                         ]);
        }

        return $this->show($question->id);


    }

    public function show($id)
    {
        return Question::where('id', $id)->with('options')->first();
    }

    public function update($id, $input)
    {
        $question = Question::where('id', $id)->create([
                                                           'text'              => $input['text'],
                                                           'max_options_value' => $input['max_options_value'],
                                                       ]);

        $question->options()->delete();

        foreach ($input['options'] as $option) {
            $question->options()->create([
                                             'text'  => $option->text,
                                             'value' => $option->value,
                                         ]);
        }

        return $this->show($question->id);
    }

    public function delete($id)
    {
        return (bool)Question::where('id', $id)->delete();
    }


}
