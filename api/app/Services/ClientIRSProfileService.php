<?php


namespace App\Services;


use App\Models\Client\Client;
use App\Models\IRS\ClientIRSProfile;

class ClientIRSProfileService extends Service
{
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
}
