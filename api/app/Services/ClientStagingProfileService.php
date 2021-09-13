<?php


namespace App\Services;


use App\Models\Client\Client;
use App\Models\Staging\ClientStagingProfile;
use App\Models\Staging\StagingOption;
use App\Models\Staging\StagingOptionResult;
use App\Traits\HelpKit;

class ClientStagingProfileService extends Service
{
    use HelpKit;

    public function calculateStaging($year, $quarter, $client, $grade = null)
    {

        if ($client->class_typ_id > 4) {
            $list = ['AAA', 'AA', 'A', 'BBB', 'BB', 'B', 'CCC/C', 'Default'];
            return $list[$grade];
        } else {
            $dateRange = $this->getDateRange($year, $quarter);
            $profile   = ClientStagingProfile::where('client_id', $client->id)
                                             ->where('created_at', '>=', $dateRange['last_date'])
                                             ->orderBy('id', 'desc')
                                             ->with('answers')
                                             ->first();
            $stage     = 0;
            if ($profile and count($profile->answers)) {
                foreach ($profile->answers as $item) {
                    $results = StagingOptionResult::where('staging_option_id', $item->staging_option_id)->get();
                    foreach ($results as $result) {
                        if ($result->with_range == 'No') {
                            $stage = max($stage, $result->stage_id);
                            break;
                        } else {
                            if ($result->range_start and $result->range_end) {
                                if ($item->value > $result->range_start and $item->value < $result->range_end) {
                                    $stage = max($stage, $result->stage_id);
                                }
                            } else if ($result->range_start) {
                                if ($item->value > $result->range_start) {
                                    $stage = max($stage, $result->stage_id);
                                }
                            } else if ($result->range_end) {
                                if ($item->value < $result->range_end) {
                                    $stage = max($stage, $result->stage_id);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $stage;
    }

    public function index($id)
    {
        $client = Client::findOrFail($id);
        return ClientStagingProfile::where('client_id', $client->id)->with('answers')->get();
    }

    public function store(array $input)
    {
        $profile = ClientStagingProfile::create(['client_id' => $input['client_id']]);

        foreach ($input['answers'] as $item) {
            $option = StagingOption::find($item['id']);
            $data   = ['staging_option_id' => $item['id']];
            if (isset($item['value']) and $option->with_value == 'Yes') {
                $data['value'] = $item['value'];
            }
            $profile->answers()->create($data);
        }

        return $this->show($profile->id);
    }

    public function show($id)
    {
        return ClientStagingProfile::whereId($id)->with('answers')->first();
    }

    public function destroy($id): bool
    {
        return (bool)ClientStagingProfile::whereId($id)->delete();
    }

}
