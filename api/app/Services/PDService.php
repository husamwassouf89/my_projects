<?php


namespace App\Services;


use App\Imports\ClientImport;
use App\Imports\PDImport;
use App\Models\Attachment;
use App\Models\PD\PD;
use App\Traits\FilesKit;
use Maatwebsite\Excel\Facades\Excel;

class PDService extends Service
{
    use FilesKit;

    public function import($input)
    {
        $pd = new PD();
        $pd->class_type_id = $input['class_type_id'];
        $pd->year = $input['year'];
        $pd->quarter = $input['quarter'];
        $pd->path = $this->saveFile(request()->file('file'));
        $pd->save();

        $attachmentIds = $input['attachment_ids'];

        if ($attachmentIds) {
            foreach ($attachmentIds as $id) {
                $attachment = Attachment::find($id);
                $attachment->attachmentable_id = $pd->id;
                $attachment->attachmentable_type = 'App\Models\PD\PD';
                $attachment->save();
            }
        }

        Excel::import(new PDImport($pd->id), request()->file('file'));

        return true;
    }

}
