<?php


namespace App\Services;


use App\Models\Client\ClassType;
use App\Models\Staging\StagingQuestion;

class StagingService extends Service
{

    public function index($id)
    {
        $type = ClassType::findOrFail($id);
        return StagingQuestion::where('class_type_id', $type->id)->with('options')->get();
    }

    public function store($input)
    {
        $question = StagingQuestion::create([
                                                'class_type_id' => $input['class_type_id'],
                                                'text'          => $input['text'],
                                            ]);

        foreach ($input['options'] as $option) {
            $question->options()->create([
                                             'text' => $option['text'],
                                             'type' => $option['type'],
                                         ]);
        }

        return $this->show($question->id);
    }

    public function show($id)
    {
        return StagingQuestion::where('id', $id)->with('options')->firstOrFail();
    }

    public function update($id, $input)
    {
        $question = StagingQuestion::findOrFail($id);

        $question->update([
                              'text' => $input['text'],
                          ]);

        foreach ($input['options'] as $option) {
            if (isset($option['id']) and $option['id'] != null) {
                $question->options()->where('id', $option['id'])
                         ->update([
                                      'text' => $option['text'],
                                      'type' => $option['type'],
                                  ]);
            } else {
                $question->options()->create([
                                                 'text' => $option['text'],
                                                 'type' => $option['type'],
                                             ]);
            }

        }

        return $this->show($question->id);
    }

    public function delete($id)
    {
        return (bool)StagingQuestion::where('id', $id)->delete();
    }
}
