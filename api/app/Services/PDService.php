<?php


namespace App\Services;


use App\Imports\ClientImport;
use App\Imports\PDImport;
use App\Models\Attachment;
use App\Models\Client\ClassType;
use App\Models\PD\PD;
use App\Traits\FilesKit;
use Carbon\Traits\Date;
use Maatwebsite\Excel\Facades\Excel;

class PDService extends Service
{
    use FilesKit;

    public function index(array $input)
    {
        $data = PD::indexSelect()->paginate($input['page_size']);
        return $this->handlePaginate($data, 'pds');
    }

    public function store($input)
    {
        $pd = new PD();
        $pd->class_type_id = $input['class_type_id'];
        $pd->year = $input['year'];
        $pd->quarter = $input['quarter'];
        $pd->path = $this->saveFile(request()->file('file'), 'pd');
        $pd->save();

        $attachmentIds = $input['attachment_ids'] ?? null;

        if ($attachmentIds) {
            foreach ($attachmentIds as $id) {
                $attachment = Attachment::find($id);
                $attachment->attachmentable_id = $pd->id;
                $attachment->attachmentable_type = 'App\Models\PD\PD';
                $attachment->save();
            }
        }

        Excel::import(new PDImport($pd), 'storage/pd/52611626793713_2021_07_20_03_08_33.xlsx');

        return true;
    }

    public function classTypeYears($id)
    {
        $allYears = [];
        for ($i = 2000; $i <= Date('Y'); $i++) {
            array_push($allYears, $i);
        }

        $years = PD::where('class_type_id', $id)->select('year')->get()->pluck('year')->toArray();
        $availableYears = array_values(array_diff($allYears, $years));

        $allQuarters = ClassType::$QUARTERS;

        $data = [];
        foreach ($availableYears as $year) {
            $quarters = PD::where('class_type_id', $id)
                          ->where('year', $year)
                          ->select('year')->get()->pluck('quarter')->toArray();
            $availableQuarters = array_values(array_diff($allQuarters, $quarters));
            array_push($data, ['year' => $year, 'quarters' => $availableQuarters]);

        }

        return $data;

    }

    public function destory($id)
    {
        if (PD::where('id', $id)->delete()) {
            return true;
        }
        return false;
    }

    public function show($id)
    {
        return PD::where('p_d_s.id', $id)->showSelect()->first();
    }


}
