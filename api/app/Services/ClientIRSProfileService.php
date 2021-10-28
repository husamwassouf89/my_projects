<?php


namespace App\Services;


use App\Models\Client\Client;
use App\Models\Client\Grade;
use App\Models\IRS\ClientIRSProfile;
use App\Traits\HelpKit;

class ClientIRSProfileService extends Service
{
    use HelpKit;

    public function index($id)
    {
        $client = Client::findOrFail($id);
        return ClientIRSProfile::where('client_id', $client->id)->with('answers')->get();
    }

    public function store(array $input)
    {
        $profile = ClientIRSProfile::create([
                                                'client_id' => $input['client_id'],
                                            ]);

        foreach ($input['answers'] as $item) {
            $profile->answers()->create(['option_id' => $item]);
        }

        return $this->show($profile->id);
    }

    public function show($id)
    {
        return ClientIRSProfile::whereId($id)->with('answers')->first();
    }

    public function destroy($id): bool
    {
        return (bool)ClientIRSProfile::whereId($id)->delete();
    }

    public function calculateIrsScore($year, $quarter, $clientId)
    {
        $dateRange = $this->getDateRange($year, $quarter);
        $profile   = ClientIRSProfile::where('client_id', $clientId)
                                     ->where('created_at', '>=', $dateRange['last_date'])
                                     ->orderBy('id', 'desc')
                                     ->with('answers')
                                     ->first();

        $score = null;
        if ($profile and count($profile->answers) > 0) {
            $score = 0;
            foreach ($profile->answers as $item) {
                $score += $item->answer_value;
            }
        }

        return $score;
    }


    public function gradePastDueDays($serialNo, $pastDueDays, $classTypeId)
    {
        if ($pastDueDays >= 90) {
            if ($pastDueDays < 180) {
                return max($serialNo, 7);
            } else {
                if ($pastDueDays < 365) {
                    return max($serialNo, 8);
                } else {
                    return 9;
                }
            }
        }
        return Grade::where('class_type_id', $classTypeId)->where('serial_no', $serialNo)->first()->name;

    }

    public function getClientGradeId($financialData, $score): int
    {
        if ($financialData == 'Sufficient financial data' or $financialData
                                                             == 'Insufficient financial data') {
            if ($score >= 90.1 and $score <= 100) {
                $serialNo = 0;
            } else if ($score >= 85.1 and $score <= 90) {
                $serialNo = 1;
            } else if ($score >= 82.1 and $score <= 85) {
                $serialNo = 2;
            } else if ($score >= 78.1 and $score <= 82) {
                $serialNo = 3;
            } else if ($score >= 74.1 and $score <= 78) {
                $serialNo = 4;
            } else if ($score >= 60.1 and $score <= 74) {
                $serialNo = 5;
            } else if ($score <= 60) {
                $serialNo = 6;
            } else {
                return -1;
            }

        } else {
            if ($score >= 85.1 and $score <= 100) {
                $serialNo = 4;
            } else if ($score >= 70.1 and $score <= 85) {
                $serialNo = 5;
            } else if ($score <= 70) {
                $serialNo = 6;
            } else {
                return -1;
            }
        }

        return $serialNo;

    }
}
