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
        $question->options()->create([
                                         'text'       => 'مربوطة',
                                         'type'       => 'Linked',
                                         'with_value' => 'Yes',
                                     ]);

        $question = $classType->stagingQuestions()->create(['text' => 'هل يوجد بيانات مالية']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'No',
                                     ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);

        $question = $classType->stagingQuestions()->create(['text' => 'التزام العميل بشروط منح التسيهلات الائتمانية']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'No',
                                     ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
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
