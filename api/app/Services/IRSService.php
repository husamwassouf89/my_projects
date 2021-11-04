<?php


namespace App\Services;


use App\Models\Client\ClassType;
use App\Models\IRS\Category;
use App\Models\IRS\IRS;

class IRSService extends Service
{
    public function index($input)
    {
        $data = IRS::where('financial_status', $input['financial_status'])
                   ->joins()->selectIndex()->paginate($input['page_size']);
        return $this->handlePaginate($data, 'irs');
    }

    public function classTypePercentage()
    {
        $classTypes = ClassType::all();

        foreach ($classTypes as $type) {
            $type->data = Category::join('i_r_s', 'i_r_s.category_id', '=', 'categories.id')
                                  ->join('questions', 'i_r_s.id', '=', 'questions.irs_id')
                                  ->where('i_r_s.class_type_id', $type->id)
                                  ->selectRaw('categories.name as category, Sum(max_options_value) as total_value')
                                  ->groupBy('category')->get();
        }

        return $classTypes;

    }

    public function store($input)
    {
        $irs             = IRS::firstOrCreate([
                                                  'class_type_id'    => $input['class_type_id'],
                                                  'category_id'      => $input['category_id'],
                                                  'financial_status' => $input['financial_status'],
                                              ]);
        $irs->percentage = $input['percentage'];
        $irs->save();

        return $this->show($input);
    }

    public function show($input)
    {
        IRS::firstOrCreate([
                               'class_type_id'    => $input['class_type_id'],
                               'category_id'      => $input['category_id'],
                               'financial_status' => $input['financial_status'],
                           ]);
        return IRS::where('class_type_id', $input['class_type_id'])
                  ->where('category_id', $input['category_id'])
                  ->where('financial_status', $input['financial_status'])
                  ->joins()
                  ->selectIndex()
                  ->first();
    }

    public function destroy($id)
    {
        return (bool)IRS::where('id', $id)->delete();
    }

}
