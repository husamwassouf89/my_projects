<?php


namespace App\Services;


use App\Models\IRS\IRS;

class IRSService extends Service
{
    public function index($input)
    {
        $data = IRS::joins()->selectIndex()->paginate($input['page_size']);
        return $this->handlePaginate($data, 'irs');
    }

    public function store($input)
    {
        $irs             = IRS::firstOrCreate([
                                           'class_type_id' => $input['class_type_id'],
                                           'category_id'   => $input['category_id'],
                                       ]);
        $irs->percentage = $input['percentage'];
        $irs->save();

        return $this->show($irs->id);
    }

    public function show($id)
    {
        return IRS::where('i_r_s.id', $id)->joins()->selectIndex()->first();
    }

    public function destroy($id)
    {
        return (bool)IRS::where('id', $id)->delete();
    }

}
