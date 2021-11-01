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
use App\Traits\FilesKit;
use App\Traits\MailSender;
use App\Traits\PDKit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class HelpController extends Controller
{
    use PDKit, FilesKit, MailSender;

    public function clearCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        return 'Done! 🌠🌠';
    }

    public function test()
    {

        self::sendEmail('philip97hd@gmail.com','welcome',[]);

        return "done";

        $date          = Carbon::createFromDate('2026-7-22');
        $valuationDate = Carbon::createFromDate('2021-3-31');
        $temp          = Carbon::createFromDate('2021-3-31');
        $temp->addDays(365);
        if ($temp < $date) {
            $date12 = $temp;
        } else {
            $date12 = $date;
        }

        $freq         = 1;
        $ead          = 115425000;
        $pd           = 0.027868167;
        $lgd          = 0.10;
        $discountRate = 0.11;
        $lecl         = 0;
        $eclM12       = 0;
        $repayments   = [];
        $day          = $date->format('d');
        $month        = $date->format('m');
        $year         = $date->format('Y');
        array_push($repayments, $date->toDateString());
        while (true) {
            if ($month <= 1) {
                $nextMonth = ($month - $freq) + 12;
            } else {
                $nextMonth = $month - $freq;
            }

            if ($nextMonth >= $month) $year = $year - 1;
            $month       = $nextMonth;
            $currentDate = Carbon::createFromDate($year, $month, $day);
            if ($currentDate <= $valuationDate) {
                $currentDate = $valuationDate;
            }
            array_push($repayments, $currentDate->toDateString());
            if ($currentDate <= $valuationDate) break;
        }
        $data            = [];
        $lastValue       = null;
        $repaymentAmount = $ead / (count($repayments) - 1);
        $two             = false;
        foreach ($repayments as $key => $repayment) {
            $value                   = [];
            $value['repayment_date'] = $repayment;
            $value['days_between']   = 0;

            if ($key != count($repayments) - 1) {
                $value['repayment_indicator'] = 1;
            } else {
                $value['repayment_indicator'] = 0;
            }
            $value['ead_end_of_period'] = 0;
            $value['repayment']         = $repaymentAmount;
            $value['days_for_discount'] = 0;
            $value['pd_cum']            = 0;
            $value['lgd']               = $lgd;
            $value['discount_rate']     = 0;
            if (count($data) != 0) {
                $value['days_between'] = $this->daysBetweenTwoDates($repayment, $repayments[$key - 1]);

                if (!$two) {
                    if ($date > $valuationDate) {
                        $value['days_for_discount'] = $this->daysBetweenTwoDates($date->toDateString(), $valuationDate->toDateString());
                    } else {
                        $value['days_for_discount'] = 360;
                    }
                    $two = true;
                } else {
                    if ($lastValue['days_for_discount'] == 0) {
                        $value['days_for_discount'] = 0;
                    } else {
                        $value['days_for_discount'] = $lastValue['days_for_discount'] - $lastValue['days_between'];
                    }
                }


                $value['repayment']         = $repaymentAmount * $value['repayment_indicator'];
                $value['ead_end_of_period'] = $lastValue['ead_end_of_period'] + $lastValue['repayment'];
                $value['pd_cum']            = 1 - pow(1 - $pd, $value['days_for_discount'] / 365);
                $value['discount_rate']     = pow(1 / (1 + $discountRate), $value['days_for_discount'] / 360);
            }


            $lastValue = $value;
            array_push($data, $value);
        }

        $data = array_reverse($data);
        foreach ($data as $key => $item) {
            if ($key > 0 and $key < count($data) - 1) {
                $data[$key]['pd_marginal'] = abs($data[$key - 1]['pd_cum'] - $item['pd_cum']);
            } else if ($key == 0) {
                $data[$key]['pd_marginal'] = $item['pd_cum'];
            } else {
                $data[$key]['pd_marginal'] = 0;
            }
            $data[$key]['el'] = $data[$key]['ead_end_of_period'] * $data[$key]['discount_rate'] * $data[$key]['lgd'] * $data[$key]['pd_marginal'];

            if ($key) {
                $data[$key]['cum_el'] = $data[$key - 1]['cum_el'] + $data[$key]['el'];
            } else {
                $data[$key]['cum_el'] = $data[$key]['el'];
            }

            if ($date12 > Carbon::createFromDate($item['repayment_date'])) {
                $temp                    = $this->daysBetweenTwoDates($date12->toDateString(), $item['repayment_date']);
                $data[$key]['temp_days'] = $temp;
                $selector                = min($temp / $item['days_between'], 1);
            } else {
                $selector = 0;
            }

            $data[$key]['12_m_selector'] = $selector;
            $data[$key]['12_m_el']       = $data[$key]['el'] * $data[$key]['12_m_selector'];

            $lecl   += $data[$key]['el'];
            $eclM12 += $data[$key]['12_m_el'];
        }

        return $data;

        $client = Client::where('class_type_id', 1)->first();
        foreach ($client->clientAccounts as $account) {
            foreach ($account->accountInfos as $info) {
                $info->irs_score   = (new ClientIRSProfileService())
                    ->calculateIrsScore($info->year, $info->quarter, $client->id);
                $grade             = (new ClientIRSProfileService())
                    ->getClientGradeId($client->financial_data, $info->irs_score);
                $info->grade       = Grade::where('class_type_id', $client->class_type_id)
                                          ->where('serial_no', $grade)->first()->name;
                $info->final_grade = (new ClientIRSProfileService())
                    ->gradePastDueDays($grade, $account->past_due_days, $client->class_type_id);
                $info->pd          = (new PDService())->getPdByYearQuarter($info->year, $info->quarter)['final_calibrated_used_PD'][$grade];

                $stage = (new ClientStagingProfileService())->calculateStaging($info->year, $info->quarter, $client, $grade);

                $info->stage = Stage::where('serial_no', $stage)->first()->name;

                return $info;
            }
        }
    }

    private function daysBetweenTwoDates($date1, $date2)
    {
        $date1 = Carbon::createFromDate($date1);
        $date2 = Carbon::createFromDate($date2);
        return $date1->floatDiffInRealDays($date2);
    }

    public function fetchPredefined()
    {
        $data                     = [];
        $data['class_types']      = ClassType::all();
        $data['categories']       = Category::all();
        $data['years']            = ClassType::getYears();
        $data['quarters']         = ClassType::$QUARTERS;
        $data['financial_status'] = Client::$FINANCIAL_STATUS;

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

