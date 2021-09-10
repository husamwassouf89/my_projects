<?php


namespace App\Services;


use App\Models\Staging\ClientStagingProfile;

class ClientStagingProfileService extends Service
{

    public function index($id)
    {
        return ClientStagingProfile::where('client_id', $id)->get();
    }

    public function store(array $input)
    {
        $profile = ClientStagingProfile::create([
                                                    'client_id' => $input['client_id'],
                                                ]);

        foreach ($input['answers'] as $item) {
            $data = ['staging_option_id' => $item['id']];
            if (isset($item['value'])) {
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
