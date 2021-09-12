<?php

namespace Database\Seeders;

use App\Models\Client\ClassType;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $classType = ClassType::where(['name' => 'Corporate'])->first();
        $question  = $classType->stagingQuestions()->create(['text' => 'عدد ايام التأخير']);
        $option    = $question->options()->create([
                                                      'text'       => 'مربوطة',
                                                      'type'       => 'Linked',
                                                      'with_value' => 'Yes',
                                                  ]);

        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 30,
                                       'stage_id'   => 1
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_start'  => 30,
                                       'range_end'  => 89,
                                       'stage_id'   => 2
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 89,
                                       'stage_id'   => 3
                                   ]);

        $question = $classType->stagingQuestions()->create(['text' => 'هل يوجد بيانات مالية']);
        $option = $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $option = $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'التعرضات موثقة بعقود اصولية']);
        $option = $question->options()->create([
                                                   'text'       => 'نعم',
                                                   'type'       => 'Yes',
                                                   'with_value' => 'No',
                                               ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $option = $question->options()->create([
                                                   'text'       => 'لا',
                                                   'type'       => 'No',
                                                   'with_value' => 'No',
                                               ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);

        $question = $classType->stagingQuestions()->create(['text' => 'التزام العميل بشروط منح التسيهلات الائتمانية']);
        $option = $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $option =$question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);

        $question = $classType->stagingQuestions()->create(['text' => 'حسابات خارج الميزانية مدفوعة عن العملاء']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'Yes',
                                     ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);

    }
}
