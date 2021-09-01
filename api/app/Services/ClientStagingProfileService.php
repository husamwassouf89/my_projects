<?php


namespace App\Services;


use App\Models\Staging\ClientStagingProfile;

class ClientStagingProfileService extends Service
{
    public function store(array $input)
    {
        $profile = ClientStagingProfile::create([
                                                    'client_id' => $input['client_id'],
                                                ]);

        foreach ($input['answers'] as $item) {
            $profile->answers()->create(['staging_option_id' => $item]);
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
