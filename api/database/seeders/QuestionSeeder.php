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
        $question = $classType->stagingQuestions()->create(['text' => 'التعرضات موثقة بعقود اصولية']);
        $option   = $question->options()->create([
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
        $option   = $question->options()->create([
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

        $question = $classType->stagingQuestions()->create(['text' => 'حسابات خارج الميزانية مدفوعة عن العملاء']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'Yes',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 30,
                                       'stage_id'   => 1
                                   ]);
        $option->results()->create([
                                       'with_range'  => 'Yes',
                                       'range_start' => 30,
                                       'range_end'   => 89,
                                       'stage_id'    => 2
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 89,
                                       'stage_id'   => 3
                                   ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'جاري مدين نشط']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'Yes',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);

        $question = $classType->stagingQuestions()->create(['text' => 'تجاوز الجاري المدين للسقف']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'Yes',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 30,
                                       'stage_id'   => 1
                                   ]);
        $option->results()->create([
                                       'with_range'  => 'Yes',
                                       'range_start' => 30,
                                       'range_end'   => 89,
                                       'stage_id'    => 2
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 89,
                                       'stage_id'   => 3
                                   ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'جمود الحساب الجاري المدين']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'Yes',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 30,
                                       'stage_id'   => 1
                                   ]);
        $option->results()->create([
                                       'with_range'  => 'Yes',
                                       'range_start' => 30,
                                       'range_end'   => 89,
                                       'stage_id'    => 2
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 89,
                                       'stage_id'   => 3
                                   ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'تأخر عن تسديد الدائن صدفة مدين']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'Yes',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 30,
                                       'stage_id'   => 1
                                   ]);
        $option->results()->create([
                                       'with_range'  => 'Yes',
                                       'range_start' => 30,
                                       'range_end'   => 89,
                                       'stage_id'    => 2
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 89,
                                       'stage_id'   => 3
                                   ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'تأخر عن تجديد عقود التسهيلات الائتمانية']);
        $question->options()->create([
                                         'text'       => 'نعم',
                                         'type'       => 'Yes',
                                         'with_value' => 'Yes',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 30,
                                       'stage_id'   => 1
                                   ]);
        $option->results()->create([
                                       'with_range'  => 'Yes',
                                       'range_start' => 30,
                                       'range_end'   => 89,
                                       'stage_id'    => 2
                                   ]);
        $option->results()->create([
                                       'with_range' => 'Yes',
                                       'range_end'  => 89,
                                       'stage_id'   => 3
                                   ]);
        $question->options()->create([
                                         'text'       => 'لا',
                                         'type'       => 'No',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);
        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'التغيرات السلبية للزبون أو بيئة عمله']);
        $option   = $question->options()->create([
                                                     'text'       => 'نعم',
                                                     'type'       => 'Yes',
                                                     'with_value' => 'No',
                                                 ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);
        $option = $question->options()->create([
                                                   'text'       => 'لا',
                                                   'type'       => 'No',
                                                   'with_value' => 'No',
                                               ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'إنخفاض قيمة الضمانات المقدمة']);
        $option   = $question->options()->create([
                                                     'text'       => 'نعم',
                                                     'type'       => 'Yes',
                                                     'with_value' => 'No',
                                                 ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);
        $option = $question->options()->create([
                                                   'text'       => 'لا',
                                                   'type'       => 'No',
                                                   'with_value' => 'No',
                                               ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'الإلتزام بشروط إعاة الهيكلة']);
        $option   = $question->options()->create([
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

        $question->options()->create([
                                         'text'       => 'غير موجود',
                                         'type'       => 'Others',
                                         'with_value' => 'No',
                                     ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'هل التصنيف في بقية المصارف مرحلة ثالثة']);
        $option   = $question->options()->create([
                                                     'text'       => 'نعم',
                                                     'type'       => 'Yes',
                                                     'with_value' => 'No',
                                                 ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);
        $option = $question->options()->create([
                                                   'text'       => 'لا',
                                                   'type'       => 'No',
                                                   'with_value' => 'No',
                                               ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);


        $question = $classType->stagingQuestions()->create(['text' => 'تعرض العميل لدعوى قضائية تكبده مبالغ كبيرة']);
        $option   = $question->options()->create([
                                                     'text'       => 'نعم',
                                                     'type'       => 'Yes',
                                                     'with_value' => 'No',
                                                 ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 2
                                   ]);
        $option = $question->options()->create([
                                                   'text'       => 'لا',
                                                   'type'       => 'No',
                                                   'with_value' => 'No',
                                               ]);
        $option->results()->create([
                                       'with_range' => 'No',
                                       'stage_id'   => 1
                                   ]);





    }
}
