<?php


namespace App\Services;


use App\Models\Client\Client;
use App\Models\Staging\ClientStagingProfile;
use App\Models\Staging\StagingOption;
use App\Traits\HelpKit;

class ClientStagingProfileService extends Service
{
    use HelpKit;

    public function calculateStagingScore($year, $quarter, $clientId): int
    {
        $dateRange = $this->getDateRange($year, $quarter);
        $profile   = ClientStagingProfile::where('client_id', $clientId)
                                         ->where('created_at', '<=', $dateRange['last_date'])
                                         ->orderBy('id', 'desc')
                                         ->with('answers')
                                         ->first();
        $score     = 0;
        foreach ($profile->answers as $item) {
            $score += $item->answer_value;
        }
        return $score;
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
