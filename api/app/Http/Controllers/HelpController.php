<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\DeleteAttachmentIdsRequest;
use App\Http\Requests\Attachment\UploadRequest;
use App\Models\Attachment;
use App\Models\Client\AccountInfo;
use App\Models\Client\ClassType;
use App\Models\Client\Client;
use App\Models\Client\ClientAccount;
use App\Models\IRS\Answer;
use App\Models\IRS\Category;
use App\Models\IRS\ClientIRSProfile;
use App\Models\Staging\Stage;
use App\Traits\FilesKit;
use App\Traits\MailSender;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class HelpController extends Controller
{

    private $map
        = [
            1  => 'A', 2 => 'B', 3 => 'C',
            4  => 'D', 5 => 'E', 6 => 'F',
            7  => 'G', 8 => 'H', 9 => 'I',
            10 => 'G', 11 => 'K', 12 => 'L',
            13 => 'M', 14 => 'N', 15 => 'P',
            16 => 'Q', 17 => 'R', 18 => 'S',
            19 => 'T', 20 => 'U', 21 => 'V',
            22 => 'W', 23 => 'X', 24 => 'W',
            25 => 'Y', 26 => 'Z',
        ];

    use FilesKit, MailSender;

    public function clearCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        return 'Done! 🌠🌠';
    }

    public function test()
    {
        $profile = ClientIRSProfile::
//                                     ->where('created_at', '>=', $dateRange['last_date'])
        orderBy('id', 'desc')
                                   ->with('answers')
                                   ->first();
        $answers = Answer::where('client_i_r_s_profile_id', $profile->id)
                         ->join('options', 'options.id', '=', 'answers.option_id')
                         ->join('questions', 'questions.id', '=', 'options.question_id')
                         ->join('i_r_s', 'i_r_s.id', '=', 'questions.irs_id')
                         ->select('value', 'i_r_s.category_id', 'i_r_s.percentage')
                         ->get();

        $score      = [];
        $finalScore = 0;
        $categories = Category::all();
        foreach ($categories as $item) {
            if (!isset($score[$item->id])) {
                $score[$item->id] = 0;
            }
        }
        if ($profile and count($answers) > 0) {
            foreach ($answers as $item) {
                $score[$item->category_id] += $item->value * $item->percentage;
            }
        }
        foreach ($score as $key => $item) {
            $finalScore += $item;
        }

        return $finalScore;
    }

    public function fetchPredefined()
    {
        $data                     = [];
        $data['class_types']      = ClassType::all();
        $data['categories']       = Category::all();
        $data['years']            = ClassType::getYears();
        $data['quarters']         = ClassType::$QUARTERS;
        $data['financial_status'] = Client::$FINANCIAL_STATUS;
        $data['stages']           = Stage::all();

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

    private function daysBetweenTwoDates($date1, $date2)
    {
        $date1 = Carbon::createFromDate($date1);
        $date2 = Carbon::createFromDate($date2);
        return $date1->floatDiffInRealDays($date2);
    }

}

