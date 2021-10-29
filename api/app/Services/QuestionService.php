<?php


namespace App\Services;


use App\Models\IRS\IRS;
use App\Models\IRS\Question;

class QuestionService extends Service
{
    public function store($input)
    {
        $irs      = IRS::firstOrCreate([
                                           'class_type_id'    => $input['class_type_id'],
                                           'category_id'      => $input['category_id'],
                                           'financial_status' => $input['financial_status'],
                                       ]);
        $question = Question::create([
                                         'irs_id'            => $irs->id,
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
        $question = Question::findOrFail($id);

        $question->update([
                              'text'              => $input['text'],
                              'max_options_value' => $input['max_options_value'],
                          ]);

        foreach ($input['options'] as $option) {
            if (isset($option['id']) and $option['id'] != null) {
                $question->options()->where('id', $option['id'])
                         ->update([
                                      'text'  => $option['text'],
                                      'value' => $option['value'],
                                  ]);
            } else {
                $question->options()->create([
                                                 'text'  => $option['text'],
                                                 'value' => $option['value'],
                                             ]);
            }

        }

        return $this->show($question->id);
    }

    public function delete($id)
    {
        return (bool)Question::where('id', $id)->delete();
    }


}
