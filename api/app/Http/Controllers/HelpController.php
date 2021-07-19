<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\DeleteAttachmentIdsRequest;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HelpController extends Controller
{

    public function clearCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return 'Done! 🌠🌠';

    }

    public function test()
    {
        \request()->validate([
                                 'n' => 'required|numeric|min:1|max:100'
                             ]);
        return "s";

    }

    public function fetchPredefined()
    {

        $data = [];

        return $this->response('success', $data, 200);
    }

    public function uploadAttachments(Request $request)
    {
        if (isset($_FILES) && count($_FILES) > 0) {
            $attachmentIds = [];
            foreach ($_FILES as $key => $value) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    $attachment = new Attachment();
                    $attachment->url = $attachment->saveFile($file);
                    $attachment->save();
                    array_push($attachmentIds, $attachment->id);
                }
            }
            return $this->response('success', $attachmentIds, 200);
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

}

