<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\DeleteAttachmentIdsRequest;
use App\Http\Requests\Attachment\UploadRequest;
use App\Models\Attachment;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\Grade;
use App\Models\IRS\Category;
use App\Models\Staging\Stage;
use App\Services\ClientIRSProfileService;
use App\Services\ClientStagingProfileService;
use App\Services\PDService;
use App\Services\StagingService;
use App\Traits\FilesKit;
use App\Traits\PDKit;
use Illuminate\Support\Facades\Artisan;

class HelpController extends Controller
{
    use PDKit, FilesKit;

    public function clearCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        return 'Done! 🌠🌠';
    }

    public function test()
    {
        $client = Client::where('class_type_id', 1)->first();
        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                $info->irs_score = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);
                $grade           = (new ClientIRSProfileService())
                    ->getClientGradeId($client->financial_data, $info->irs_score);
                $info->grade     = Grade::where('class_type_id', $client->class_type_id)
                                        ->where('serial_no', $grade)->first()->name;
                $info->final_grade = (new ClientIRSProfileService())
                    ->gradePastDueDays($grade, $account->past_due_days, $client->class_type_id);
                $info->pd = (new PDService())->getPdByYearQuarter($info->year,$info->quarter)['final_calibrated_used_PD'][$grade];

                $stage = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client,$grade);

                $info->stage = Stage::where('serial_no',$stage)->first()->name;

                return $info;
            }
        }
    }

    public function fetchPredefined()
    {
        $data                = [];
        $data['class_types'] = ClassType::all();
        $data['categories']  = Category::all();
        $data['years']       = ClassType::getYears();
        $data['quarters']    = ClassType::$QUARTERS;

        return $this->response('success', $data, 200);
    }


    public function uploadAttachments(UploadRequest $request)
    {
        if (isset($_FILES) && count($_FILES) > 0) {
            $data = [];
            foreach ($_FILES as $key => $value) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    if ($request->type == 'attachments') {
                        $attachment       = new Attachment();
                        $attachment->path = $this->saveFile($file);
                        $attachment->save();
                        array_push($data, $attachment->id);
                    } else {
                        $path = $this->saveFile($file, $request->type);
                        array_push($data, $path);
                    }

                }
            }
            return $this->response('success', $data, 200);
        }
        return $this->response('failed', null, 404);
    }

    public function deleteAttachments(DeleteAttachmentIdsRequest $request)
    {
        if (Attachment::whereIn('id', $request->ids)->delete()) {
            return $this->response('success');

        }
        return $this->response('failed', null, 500);
    }

    private function getQuarters()
    {

    }


}

