<?php


namespace App\Services;


use App\Models\Attachment;
use App\Models\Client\Client;
use App\Models\Client\Grade;
use App\Models\IRS\Answer;
use App\Models\IRS\Category;
use App\Models\IRS\ClientIRSProfile;
use App\Models\IRS\IRS;
use App\Models\Staging\Stage;
use App\Traits\HelpKit;

class ClientIRSProfileService extends Service
{
    use HelpKit;

    public function index($id)
    {
        $client = Client::findOrFail($id);
        $data   = ClientIRSProfile::where('client_id', $client->id)->with('answers')->get();

        $categories = Category::all();

        $arr = [];
        foreach ($data as $item1) {

            $ok      = false;
            $answers = $item1->answers;
            array_push($arr, count($answers));

            $score      = [];
            $finalScore = 0;
            foreach ($categories as $item) {
                if (!isset($score[$item->id])) {
                    $score[$item->id] = 0;
                }
            }
            if (count($answers) > 0) {
                foreach ($answers as $item) {
                    $irs                      = IRS::join('questions', 'questions.irs_id', '=', 'i_r_s.id')
                                                   ->where('questions.id', $item->question_id)->first();
                    $score[$irs->category_id] += $item->answer_value * $irs->percentage;

                    if (!$ok) {
                        $ok     = true;
                        $status = $irs->financial_status;
                    }
                }
            }
            foreach ($score as $item) {
                $finalScore += $item;
            }
            $item1->financial_status = $status;
            $grades                  = $this->getGradeAndFinalGrade($status, $client, $finalScore);
            $item1->grade            = $grades['grade'];
            $item1->final_grade      = $grades['final_grade'];
            $item1->final_score      = round($finalScore, 3);

        }
        return $data;
    }

    public function getGradeAndFinalGrade($financialStatus, $client, $score)
    {

        if(!$client->grade_id){
            $serialNo   = $this->getClientGradeId($financialStatus, $score);
            $grade      = Grade::where('class_type_id', $client->class_type_id)->where('serial_no', $serialNo)->first();
            $grade      = $grade->name ?? "N/A";
            $finalGrade = $this->gradePastDueDays($serialNo, $client->past_due_dasy, $client->class_type_id);
        } else{
            $temp = Grade::find($client->grade_id);
            $grade = $temp->name ?? "WRONG VALUE";
            $finalGrade = $temp->name ?? "WRONG VALUE";
        }

        return ['grade' => $grade, 'final_grade' => $finalGrade];
    }

    public function getClientGradeId($financialData, $score): int
    {
        $serialNo = -1;
        if ($financialData == Client::$FINANCIAL_STATUS[1] or $financialData
                                                              == Client::$FINANCIAL_STATUS[2]) {
            if ($score >= 90.1 and $score <= 100) {
                $serialNo = max($serialNo, 0);
            } else if ($score >= 85.1 and $score <= 90) {
                $serialNo = max($serialNo, 1);
            } else if ($score >= 82.1 and $score <= 85) {
                $serialNo = max($serialNo, 2);
            } else if ($score >= 78.1 and $score <= 82) {
                $serialNo = max($serialNo, 3);
            } else if ($score >= 74.1 and $score <= 78) {
                $serialNo = max($serialNo, 4);
            } else if ($score >= 60.1 and $score <= 74) {
                $serialNo = max($serialNo, 5);
            } else if ($score <= 60) {
                $serialNo = max($serialNo, 6);
            }

        } else {
            if ($score >= 85.1 and $score <= 100) {
                $serialNo = max($serialNo, 4);
            } else if ($score >= 70.1 and $score <= 85) {
                $serialNo = max($serialNo, 5);
            } else if ($score <= 70) {
                $serialNo = max($serialNo, 6);
            }
        }
        return $serialNo;
    }

    public function gradePastDueDays($serialNo, $pastDueDays, $classTypeId)
    {
        if ($classTypeId == 4) {
            if ($pastDueDays < 30) {
                $serialNo = max($serialNo, 0);
            } else if ($pastDueDays >= 30 and $pastDueDays < 90) {
                $serialNo = max($serialNo, 1);
            } else {
                $serialNo = 2;
            }
        } else {
            if ($pastDueDays >= 90) {
                if ($pastDueDays < 180) {
                    $serialNo = max($serialNo, 7);
                } else {
                    if ($pastDueDays < 365) {
                        $serialNo = max($serialNo, 8);
                    } else {
                        $serialNo = 9;
                    }
                }
            }
        }


        return Grade::where('class_type_id', $classTypeId)->where('serial_no', $serialNo)->first()->name ?? "N/A";

    }

    public function store(array $input)
    {
        $profile = ClientIRSProfile::create([
                                                'client_id' => $input['client_id'],
                                            ]);

        foreach ($input['answers'] as $item) {
            $profile->answers()->create(['option_id' => $item]);
        }

        $attachmentIds = $input['attachment_ids'] ?? null;

        if ($attachmentIds) {
            foreach ($attachmentIds as $id) {
                $attachment                      = Attachment::find($id);
                $attachment->attachmentable_id   = $profile->id;
                $attachment->attachmentable_type = 'App\Models\IRS\ClientIRSProfile';
                $attachment->save();
            }
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
                                     ->first();

        if (!$profile) {
            $profile = ClientIRSProfile::where('client_id', $clientId)
//                                         ->where('created_at', '>=', $dateRange['last_date'])
                                       ->orderBy('id', 'desc')
                                       ->first();
        }

        if ($profile) {
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

        return null;
    }
}
